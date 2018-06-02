<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/2 0002
 * Time: 11:51
 */

namespace Ulue\Annotations\Node;

/**
 * Class DocCommentParser
 * @package Ulue\Annotations\Dode
 */
class DocComment
{
    /**
     * @var string Raw docBlock string
     */
    public $docBlock;

    /**
     * @var string
     */
    public $summary;

    /**
     * @var string
     */
    public $description;

    /**
     * @var array
     */
    public $tags = [];

    /**
     * constructor.
     * @param string $docBlock
     */
    public function __construct(string $docBlock)
    {
        $this->docBlock = $docBlock;
    }

    /**
     * this is summary text
     *
     * this is description ...
     * second line, this is description ...
     *
     * @param string $docBlock
     * @return string
     */
    public static function filterDocComment(string $docBlock): string
    {
        $docBlock = \str_replace("\r\n", "\n", \trim($docBlock, "/ \n"));

        // 去除所有的 * 符号
        $filtered = (string)\str_replace("\r", '',
            \trim(\preg_replace('/^\s*\**( |\t)?/m', '', $docBlock))
        );

        $filtered = \trim($filtered, '/* ');

        return \trim($filtered);
    }

    /**
     * @param string $docBlock
     * @return array
     * - 0 完整模式的所有匹配
     * - 1 由 `([A-Za-z]\w+)` 匹配到的标签 names
     * - 2 由 `([^\)]*)` 匹配到的参数 list
     */
    public static function parseToTagStrings(string $docBlock): array
    {
        $matches = [];

        \preg_match_all('/@([A-Za-z]\w+)[\s\t]*\(([^\)]*)\)[\s\t]*\r?/m', $docBlock, $matches);

        return $matches;
    }
}
