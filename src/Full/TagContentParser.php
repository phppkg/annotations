<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/2 0002
 * Time: 11:49
 */

namespace Ulue\Annotations;

/**
 * Class TagStringParser
 * @package Ulue\Annotations
 */
class TagContentParser
{
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
    private $previou;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var array
     */
    private $data = [];

    /**
     * TagStringParser constructor.
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = \trim($content);
        $this->length = \mb_strlen($content, 'UTF-8');
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
