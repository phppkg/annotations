<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018/5/14
 * Time: 下午8:24
 */

namespace Ulue\Annotations;

/**
 * Class AnnotationParser
 * @package Ulue\Annotations
 */
final class AnnotationParser extends AbstractParser
{
    /**
     * @param string $docBlock
     * @return array
     */
    public static function parseToTagStrings(string $docBlock): array
    {
        $matches = [];

        \preg_match_all('/@([A-Za-z]\w+)[\s\t]*\(([^\)]*)\)[\s\t]*\r?/m', $docBlock, $matches);

        return $matches;
    }

    /**
     * Parse annotations
     *
     * @param string $docBlock The doc block string.
     * @param bool $nameAsKey use tag name as index key.(NOTICE: repeat tag will override)
     * @return array parsed annotations data
     * @throws \InvalidArgumentException
     */
    public function parse(string $docBlock, bool $nameAsKey = false): array
    {
        $annotations = [];

        if (!$docBlock = trim($docBlock, '/')) {
            return $annotations;
        }

        // 去除所有的 * 符号
        if (!$docBlock = self::filterDocComment($docBlock)) {
            return $annotations;
        }

        /** @var array[] $matches */
        if ($matches = self::parseToTagStrings($docBlock)) {
            foreach ($matches[1] as $index => $name) {
                // skip ignored
                if (isset(self::$ignoredTags[$name])) {
                    continue;
                }

                if (!isset($matches[2][$index])) {
                    continue;
                }

                // 多行参数 去掉换行符
                $argsParts = \trim(\str_replace("\n", '', $matches[2][$index]));
                $argsData = self::parseTagContent($argsParts);
                $annotations[] = [$name, $argsData];
            }

            unset($matches);

            // use tag name as index key
            if ($nameAsKey) {
                $tagMap = [];

                foreach ($annotations as list($name, $data)) {
                    if (isset(self::$allowMultiTags[$name])) {
                        $tagMap[$name][] = $data;
                    } else {
                        $tagMap[$name] = $data;
                    }
                }

                unset($annotations);
                return $tagMap;
            }
        }

        return $annotations;
    }

    /**
     * Parse individual annotation arguments
     * @param  string $content arguments string
     * @return array|string  annotated arguments
     * @throws \InvalidArgumentException
     */
    public static function parseTagContent(string $content): array
    {
        if (!$content) {
            return [];
        }

        $data = [];
        $len = \strlen($content);
        $i = 0;
        $var = $val = '';
        $level = 1;

        $type = 'plain';
        $prevDelimiter = $nextDelimiter = '';
        // $nextToken = '';
        $composing = $quoted = false;
        $delimiter = null;
        $tokens = ['"', '"', '{', '}', ',', '='];

        while ($i < $len) {
            $c = $content[$i++] ?? '';

            if ($c === '\'' || $c === '"') {
                $delimiter = $c;
                //open delimiter
                if (!$composing && empty($prevDelimiter) && empty($nextDelimiter)) {
                    $prevDelimiter = $nextDelimiter = $delimiter;
                    $val = '';
                    $composing = true;
                    $quoted = true;
                } else {
                    // close delimiter
                    if ($c !== $nextDelimiter) {
                        throw new \InvalidArgumentException(sprintf(
                            'Parse Error: enclosing error -> expected: [%s], given: [%s]',
                            $nextDelimiter, $c
                        ));
                    }

                    // validating syntax
                    if ($i < $len) {
                        if (',' !== $content[$i]) {
                            throw new \InvalidArgumentException(sprintf(
                                'Parse Error: missing comma separator near: ...%s<--',
                                \substr($content, $i - 10, $i)
                            ));
                        }
                    }

                    $prevDelimiter = $nextDelimiter = '';
                    $composing = false;
                    $delimiter = null;
                }
            } elseif (!$composing && \in_array($c, $tokens, true)) {
                switch ($c) {
                    case '=':
                        $prevDelimiter = $nextDelimiter = '';
                        $level = 2;
                        $composing = false;
                        $type = 'assoc';
                        $quoted = false;
                        break;
                    case ',':
                        $level = 3;

                        // If composing flag is true yet,
                        // it means that the string was not enclosed, so it is parsing error.
                        if ($composing === true && !empty($prevDelimiter) && !empty($nextDelimiter)) {
                            throw new \InvalidArgumentException(sprintf(
                                'Parse Error: enclosing error -> expected: [%s], given: [%s]',
                                $nextDelimiter, $c
                            ));
                        }

                        $prevDelimiter = $nextDelimiter = '';
                        break;
                    case '{':
                        $subC = '';
                        $subComposing = true;

                        while ($i <= $len) {
                            $c = $content[$i++] ?? '';

                            if ($delimiter !== null && $c === $delimiter) {
                                throw new \InvalidArgumentException(sprintf(
                                    'Parse Error: Composite variable is not enclosed correctly.'
                                ));
                            }

                            if ($c === '}') {
                                $subComposing = false;
                                break;
                            }
                            $subC .= $c;
                        }

                        // if the string is composing yet means that the structure of var. never was enclosed with '}'
                        if ($subComposing) {
                            throw new \InvalidArgumentException(sprintf(
                                "Parse Error: Composite variable is not enclosed correctly. near: ...%s'",
                                $subC
                            ));
                        }

                        $val = self::parseTagContent($subC);
                        break;
                }
            } else {
                if ($level === 1) {
                    $var .= $c;
                } elseif ($level === 2) {
                    $val .= $c;
                }
            }

            if ($level === 3 || $i === $len) {
                if ($type === 'plain') {//  && $i === $len
                    $data[] = self::castValue($var);
                } else {
                    $data[trim($var)] = self::castValue($val, !$quoted);
                }

                $level = 1;
                $var = $val = '';
                $composing = $quoted = false;
            }
        }

        return $data;
    }

    /**
     * Try determinate the original type variable of a string
     *
     * @param  string|array|bool $val string containing possibles variables that can be cast to bool or int
     * @param  boolean $trim indicate if the value passed should be trimmed after to try cast
     * @return mixed         returns the value converted to original type if was possible
     */
    private static function castValue($val, $trim = false)
    {
        if (\is_array($val)) {
            foreach ($val as $key => $value) {
                $val[$key] = self::castValue($value);
            }
        } elseif (\is_string($val)) {
            if ($trim) {
                $val = \trim($val);
            }

            $tmp = \strtolower($val);

            if ($tmp === 'false' || $tmp === 'true') {
                $val = $tmp === 'true';
            } elseif (\is_numeric($val)) {
                return $val + 0;
            }

            unset($tmp);
        }

        return $val;
    }

}
