<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/20 0020
 * Time: 16:46
 */

namespace Ulue\Annotations;

/**
 * Class AnnotationObject
 * @package Ulue\Annotations
 */
class AnnotationObject
{
    /**
     * @var array
     */
    protected $data = [];

    public function __construct(array $args = [])
    {
        $this->data = $args;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function get($key, $default = null)
    {
        if (empty($this->data[$key])) {
            return $default;
        }

        return $this->data[$key];
    }

    public function exists($key): bool
    {
        return !empty($this->data[$key]);
    }
}

