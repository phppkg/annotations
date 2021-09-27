<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/2 0002
 * Time: 11:51
 */

namespace PhpPkg\Annotations\Node;

/**
 * Class DocCommentParser
 * @package PhpPkg\Annotations\Dode
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
}
