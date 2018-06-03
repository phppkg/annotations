<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/2 0002
 * Time: 11:49
 */

namespace Ulue\Annotations\Full;

use Ulue\Annotations\DocBlockHelper;

/**
 * Class TagStringParser
 * @package Ulue\Annotations\Full
 */
class TagContentParser
{
    const TOKENS = ['"', '"', '{', '}', ',', '='];

    const SINGLE_QUOTES = "'";
    const DOUBLE_QUOTES = '"';

    const COMMA = ',';
    const EQUAL_SIGN = '=';
    const CURLY_BRACES_L = '{';
    const CURLY_BRACES_R = '}';

    /**
     * @var string
     */
    private $content;

    /**
     * @var int
     */
    private $length;

    /**
     * @var string  Previous char
     */
    private $prevChar;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var string
     */
    private $encoding = 'UTF-8';

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param string $content
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function handle(string $content): array
    {
        $self = new static($content);

        return $self->parse();
    }

    /**
     * TagStringParser constructor.
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = \trim($content);
        $this->length = \mb_strlen($content, $this->encoding);
    }

    const TYPE_TXT = 'plain'; // "some ..."
    const TYPE_ARR = 'array'; // {v0,v1}
    const TYPE_OBJ = 'object'; // {k=v}

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    public function parse(): array
    {
        if (!$len = $this->length) {
            return [];
        }

        $type = self::TYPE_TXT;
        $level = 1;
        $tokens = self::TOKENS;
        $delimiter = null;

        $data = [];
        $var = $val = '';
        // composing - 表明一个结构是否完善 ' -> ', " -> ", { -> }
        $composing = $quoted = false;
        $prevDelimiter = $nextDelimiter = '';

        $i = 0;

        while ($i < $len) {
            // $i++ processing ...
            $this->offset = $i;
            $char = \mb_substr($this->content, $i++, 1, $this->encoding);

            if ($char === self::SINGLE_QUOTES || $char === self::DOUBLE_QUOTES) {
                $delimiter = $char;
                // open delimiter, init some vars
                if (!$composing && !$prevDelimiter && !$nextDelimiter) {
                    $prevDelimiter = $nextDelimiter = $delimiter;
                    $val = '';
                    $composing = true;
                    $quoted = true;
                } else {
                    // close delimiter ' "
                    if ($char !== $nextDelimiter) {
                        throw new \InvalidArgumentException(sprintf(
                            'Parse Error: enclosing error -> expected: [%s], given: [%s]',
                            $nextDelimiter, $char
                        ));
                    }

                    // validating syntax - 检查 delimiter('/") 之后是不是逗号 `,`
                    while ($i < $len) {
                        $nextChar = $this->getChar($i);

                        // 忽略 " 到 结构完结点 , 之间的 空格 换行 TAB 等无效字符
                        if ('' === \trim($nextChar)) {
                            $i++;
                        } elseif (self::COMMA !== $nextChar) {
                            throw new \InvalidArgumentException(sprintf(
                                'Parse Error: missing comma separator near(next %s): ...%s<--',
                                $nextChar,
                                \mb_substr($this->content, $i - 12, $i, $this->encoding)
                            ));
                            // 是逗号 OK
                        } else {
                            break;
                        }
                    }

                    $prevDelimiter = $nextDelimiter = '';
                    $composing = false;
                    $delimiter = null;
                }
            } elseif (!$composing && \in_array($char, $tokens, true)) {
                switch ($char) {
                    case '=': // split key value
                        $type = self::TYPE_ARR;
                        $level = 2;
                        $composing = $quoted = false;
                        $prevDelimiter = $nextDelimiter = '';
                        break;
                    case ',': // end a node.
                        $level = 3;

                        // If composing flag is true yet,
                        // it means that the string was not enclosed, so it is parsing error.
                        if ($composing === true && !empty($prevDelimiter) && !empty($nextDelimiter)) {
                            throw new \InvalidArgumentException(sprintf(
                                'Parse Error: enclosing error -> expected: [%s], given: [%s]',
                                $nextDelimiter, $char
                            ));
                        }

                        $prevDelimiter = $nextDelimiter = '';
                        break;
                    case '{': // start a sub content
                        $type = self::TYPE_ARR;
                        $subContent = '';
                        $subComposing = true;

                        while ($i <= $len) {
                            // $i++ processing ...
                            $char = $this->getChar($i++);

                            if ($delimiter !== null && $char === $delimiter) {
                                throw new \InvalidArgumentException(sprintf(
                                    'Parse Error: Composite variable is not enclosed correctly.'
                                ));
                            }

                            // end sub content
                            if ($char === '}') {
                                $subComposing = false;
                                break;
                            }

                            $subContent .= $char;
                        }

                        // if the string is composing yet means that the structure of var. never was enclosed with '}'
                        if ($subComposing) {
                            throw new \InvalidArgumentException(sprintf(
                                "Parse Error: Composite variable is not enclosed correctly. near: ...%s'",
                                $subContent
                            ));
                        }
                        // handle sub-content
                        $val = static::handle($subContent);
                        //\var_dump($subContent, $val);
                        break;
                }
            } else {
                if ($level === 1) {
                    $var .= $char;
                } elseif ($level === 2 && !\is_array($val)) {
                    $val .= $char;
                }
            }

            // collect values
            if ($level === 3 || $i === $len) {
                $var = \trim($var);

                if ($type === self::TYPE_TXT) {//  && $i === $len
                    $data[] = DocBlockHelper::castValue($var);
                } elseif ($var === '') {
                    $data[] = DocBlockHelper::castValue($val, !$quoted);
                } else {
                    $data[$var] = DocBlockHelper::castValue($val, !$quoted);
                }

                // reset
                $type = self::TYPE_TXT;
                $level = 1;
                $var = $val = '';
                $composing = $quoted = false;
            }

            $this->prevChar = $char;
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function doParse(): array
    {
        return [];
    }

    /**
     * @param int $offset
     * @return string
     */
    public function getChar(int $offset): string
    {
        if ($offset >= $this->length) {
            return '';
        }

        return \mb_substr($this->content, $offset, 1, $this->encoding);
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
