<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018/5/14
 * Time: 下午8:24
 */

namespace PhpComLab\Annotations;

use InvalidArgumentException;
use PhpComLab\Annotations\Full\TagContentParser;
use function preg_match_all;
use function vdump;

/**
 * Class AnnotationParser
 * @package PhpComLab\Annotations
 */
final class AnnotationParser extends AbstractParser
{
    /**
     * @param string $docBlock
     * @return array
     * [
     *  ['tagName', 'tagContent'],
     * ]
     */
    public function parseToTagStrings(string $docBlock): array
    {
        $tagStrings = $matches = [];

        // bug：当 tag 内部含有 右括号时，匹配出来会缺少后面的数据
        // m - 多行支持
        // preg_match_all('/@([A-Za-z]\w+)\(([^\)]*)\)[\s\t]*\r?/m', $docBlock, $matches);
        // preg_match_all('/@([A-Za-z]\w+)\(([\s.]*)\)[\s\t]*\r?/m', $docBlock, $matches);
        // preg_match_all('/@([A-Za-z]\w+)\(([\s\S]+)\)[\s\t]*\r?/m', $docBlock, $matches);
        preg_match_all('/@([A-Za-z]\w+)\(([^@]*)\)[\s\t]*\r?/m', $docBlock, $matches);

        /** @var array[] $matches */
        if ($matches) {
            foreach ($matches[1] as $index => $name) {
                // skip ignored
                if (isset(self::$ignoredTags[$name])) {
                    continue;
                }

                if (!isset($matches[2][$index])) {
                    continue;
                }

                $tagStrings[] = [$name, $matches[2][$index]];
            }
        }

        return $tagStrings;
    }

    /**
     * @param string $content
     * @param string $tag
     * @return array
     * @throws InvalidArgumentException
     */
    public function parseTagContent(string $content, string $tag): array
    {
        return TagContentParser::handle($content);
    }

    /**
     * @from \phpDocumentor\Reflection\DocBlockFactory::splitTagBlockIntoTagLines
     * @param string $tags
     * @return string[]
     */
    private function splitTagBlockIntoTagLines(string $tags): array
    {
        $result = [];
        foreach (\explode("\n", $tags) as $tag_line) {
            if (isset($tag_line[0]) && ($tag_line[0] === '@')) {
                $result[] = $tag_line;
            } else {
                $result[\count($result) - 1] .= "\n" . $tag_line;
            }
        }

        return $result;
    }
}
