<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/20 0020
 * Time: 18:51
 */

require dirname(__DIR__) . '/tests/boot.php';

function parse($text)
{
    if (preg_match_all('/@([^@\n\r\t]*)/', $text, $globalMatches) > 0) {
        $ret = [];
        // var_dump($globalMatches);
        /** @var array[] $globalMatches */
        foreach ($globalMatches[1] as $annotationText) {
            preg_match('/([a-zA-Z0-9]+)/', $annotationText, $localMatches);

            // if (\in_array($localMatches[1], self::$ignoredAnnotations, true)) {
            //     continue;
            // }

            $name = $localMatches[1];
            $annotation = [];
            $optsStart = strpos($annotationText, '(');

            if ($optsStart !== false) {
                $optsEnd = strrpos($annotationText, ')');
                $optsLength = $optsEnd - $optsStart - 1;
                $opts = trim(substr($annotationText, $optsStart + 1, $optsLength));

                /**
                 * @var string $key
                 * @var array $values
                 */
                foreach (_parseOptions($opts) as $key => $values) {
                    foreach ($values as $value) {
                        $annotation[$key] = $value;
                    }
                }
            }

            $ret[$name] = $annotation;
        }

        return $ret;
    }

    return null;
}

function _parseOptions(string $optionsText)
{
    // var_dump($optionsText);
    $total = preg_match_all(
        '/([^=,]*)=[\s]*([\s]*"[^"]+"|\{[^\{\}]+\}|[^,"]*[\s]*)/', $optionsText, $matches
    );

    $options = [];

    if ($total > 0) {
        for ($i = 0; $i < $total; $i++) {
            $key = trim($matches[1][$i]);
            $value = str_replace('"', '', trim($matches[2][$i]));
            $options[$key] = [];

            if (strpos($value, '{') === 0) {
                $value = substr($value, 1, -1);
                $value = explode(',', $value);
                foreach ($value as $k => $v) {
                    $options[$key][] = trim($v);
                }
            } else {
                $options[$key][] = $value;
            }
        }
    }

    return $options;
}

// form yii2
function getTags($comment)
{
    // "@description \n" .
    $comment = str_replace("\r", '',
            trim(preg_replace('/^\s*\**( |\t)?/m', '', trim($comment, '/')))
        );

    $tags = [];
    $parts = preg_split('/^\s*@/m', $comment, -1, PREG_SPLIT_NO_EMPTY);
var_dump($parts, $comment);
    foreach ($parts as $part) {
        if (preg_match('/^(\w+)(.*)/ms', trim($part), $matches)) {
            $name = $matches[1];
            if (!isset($tags[$name])) {
                $tags[$name] = trim($matches[2]);
            } elseif (\is_array($tags[$name])) {
                $tags[$name][] = trim($matches[2]);
            } else {
                $tags[$name] = [$tags[$name], trim($matches[2])];
            }
        }
    }

    return $tags;
}

$t1 = <<<DOC
/**
 * some text
 * @MultiLine(
 *   name="myBean", 
 *   id=345, 
 *   status=false, 
 *   map={a = v1,b = v2}
 * )
 */
DOC;

var_dump(parse($t1), getTags($t1));
