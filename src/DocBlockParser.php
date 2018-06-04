<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/2 0002
 * Time: 12:16
 */

namespace Ulue\Annotations;

/**
 * Class DocBlockParser
 * @package Ulue\Annotations
 */
class DocBlockParser extends AbstractParser
{
    /**
     * @var string e.g 'summary' or 'description'
     */
    public $defaultTag = 'description';

    /**
     * @var callable
     */
    private $tagContentParser;

    /**
     * Parses the comment block into tags.
     * @param string $comments The comment block text
     * @return array The parsed tags
     */
    public function parseToTagStrings(string $comments): array
    {
        if (!$filtered = self::filterDocComment($comments)) {
            return [];
        }

        $tagStrings = [];
        $comments = "@{$this->defaultTag} {$filtered}";
        $tagParts = \preg_split('/^\s*@/m', $comments, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($tagParts as $part) {
            if (\preg_match('/^(\w+)(.*)/ms', \trim($part), $matches)) {
                $name = $matches[1];

                // skip ignored
                if (isset(self::$ignoredTags[$name])) {
                    continue;
                }

                $tagStrings[] = [$name, \trim($matches[2])];
            }
        }

        return $tagStrings;
    }

    /**
     * @param string $content
     * @param string $tag
     * @return array
     */
    public function parseTagContent(string $content, string $tag): array
    {
        if ($parser = $this->tagContentParser) {
            return $parser($content, $tag);
        }

        return [$content];
    }

    /**
     * Returns the first line of docBlock.
     *
     * @param string $comments
     * @return string
     */
    public static function firstLine(string $comments): string
    {
        $docLines = preg_split('~\R~u', $comments);

        if (isset($docLines[1])) {
            return trim($docLines[1], "/\t *");
        }

        return '';
    }

    /**
     * Returns full description from the doc-block.
     * If have multi line text, will return multi line.
     *
     * @param string $comments
     * @return string
     */
    public static function description(string $comments): string
    {
        $comments = \str_replace("\r", '', \trim(\preg_replace('/^\s*\**( |\t)?/m', '', trim($comments, '/'))));

        if (\preg_match('/^\s*@\w+/m', $comments, $matches, PREG_OFFSET_CAPTURE)) {
            $comments = \trim(substr($comments, 0, $matches[0][1]));
        }

        return $comments;
    }

    /**
     * @return callable
     */
    public function getTagContentParser(): callable
    {
        return $this->tagContentParser;
    }

    /**
     * @param callable $tagContentParser
     */
    public function setTagContentParser(callable $tagContentParser)
    {
        $this->tagContentParser = $tagContentParser;
    }
}
