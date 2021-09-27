<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/20 0020
 * Time: 16:46
 */

namespace PhpPkg\Annotations;

/**
 * Class AnnotationObject
 * @package PhpPkg\Annotations
 */
class AnnotationObject
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Class constructor.
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->data = $args;
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if (empty($this->data[$key])) {
            return $default;
        }

        return $this->data[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    public function exists($key): bool
    {
        return !empty($this->data[$key]);
    }
}

