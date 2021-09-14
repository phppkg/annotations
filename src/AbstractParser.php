<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/2 0002
 * Time: 14:44
 */

namespace PhpComLab\Annotations;

use InvalidArgumentException;
use function preg_replace;
use function str_replace;
use function trim;
use function vdump;

/**
 * Class AbstractParser
 *
 * @package PhpComLab\Annotations
 */
abstract class AbstractParser
{
    /**
     * @var array
     */
    protected static $ignoredTags = [
        'Annotation'              => 1,
        'abstract'                => 1,
        'access'                  => 1,
        'api'                     => 1,
        'author'                  => 1,
        'category'                => 1,
        'copyright'               => 1,
        'codeCoverageIgnoreStart' => 1,
        'codeCoverageIgnoreEnd'   => 1,
        'deprecated'              => 1,
        'email'                   => 1,
        'example'                 => 1,
        'exception'               => 1,
        'final'                   => 1,
        'filesource'              => 1,
        'global'                  => 1,
        'ignore'                  => 1,
        'inheritdoc'              => 1,
        'internal'                => 1,
        'license'                 => 1,
        'link'                    => 1,
        'magic'                   => 1,
        'method'                  => 1,
        'name'                    => 1,
        'override'                => 1,
        'package'                 => 1,
        'param'                   => 1,
        'private'                 => 1,
        'property'                => 1,
        'return'                  => 1,
        'see'                     => 1,
        'since'                   => 1,
        'static'                  => 1,
        'subpackage'              => 1,
        'throws'                  => 1,
        'throw'                   => 1,
        'todo'                    => 1,
        'tutorial'                => 1,
        'uses'                    => 1,
        'version'                 => 1,
    ];

    /**
     * [tag name => 1]
     *
     * @var array
     */
    protected static $allowMultiTags = [];

    /**
     * @return self
     */
    public static function make(): self
    {
        return new static();
    }

    /**
     * this is summary text
     *
     * this is description ...
     * second line, this is description ...
     *
     * @param string $docBlock
     *
     * @return string
     */
    public static function filterDocComment(string $docBlock): string
    {
        $docBlock = str_replace("\r\n", "\n", trim($docBlock, "/ \n"));

        // 去除所有的 * 符号
        $filtered = (string)str_replace("\r", '',
            trim(preg_replace('/^\s*\**( |\t)?/m', '', $docBlock))
        );

        $filtered = trim($filtered, '/* ');

        return trim($filtered);
    }

    /**
     * Parse annotations/doc-block
     *
     * @param string $docBlock The doc block string.
     * @param bool $nameAsKey use tag name as index key.(NOTICE: repeat tag will override)
     *
     * @return array parsed annotations data
     * @throws InvalidArgumentException
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

        if ($tagStrings = $this->parseToTagStrings($docBlock)) {
            foreach ($tagStrings as [$name, $content]) {
                $annotations[] = [$name, $this->parseTagContent($content, $name)];
            }
            unset($tagStrings);

            // use tag name as index key
            if ($nameAsKey) {
                $tagMap = [];
                foreach ($annotations as [$name, $data]) {
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
     * @param string $docBlock
     *
     * @return array
     * [
     *  ['tagName', 'tagContent'],
     * ]
     */
    abstract public function parseToTagStrings(string $docBlock): array;

    /**
     * @param string $content Tag content
     * @param string $tag Tag name
     *
     * @return array
     */
    abstract public function parseTagContent(string $content, string $tag): array;

    /*************************************************************************************
     * getter/setter methods
     ************************************************************************************/

    /**
     * @return array
     */
    public static function getIgnoredTags(): array
    {
        return self::$ignoredTags;
    }

    /**
     * @param string[] $ignoredTags
     */
    public static function setIgnoredTags(array $ignoredTags): void
    {
        foreach ($ignoredTags as $tag) {
            self::$ignoredTags[$tag] = 1;
        }
    }

    /**
     * @param string[]|string $tagNames
     */
    public static function notIgnoreTags($tagNames): void
    {
        foreach ((array)$tagNames as $tag) {
            if (isset(self::$ignoredTags[$tag])) {
                unset(self::$ignoredTags[$tag]);
            }
        }
    }

    /**
     * @param array $ignoredTags
     */
    public static function addIgnoredTags(array $ignoredTags): void
    {
        self::setIgnoredTags($ignoredTags);
    }

    /**
     * @param string $name
     */
    public static function addIgnoredTag(string $name): void
    {
        self::$ignoredTags[$name] = 1;
    }

    /**
     * @return array
     */
    public static function getAllowMultiTags(): array
    {
        return self::$allowMultiTags;
    }

    /**
     * @param array $allowMultiTags
     */
    public static function setAllowMultiTags(array $allowMultiTags): void
    {
        foreach ($allowMultiTags as $tag) {
            self::$allowMultiTags[$tag] = 1;
        }
    }
}
