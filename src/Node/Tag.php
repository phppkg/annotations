<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/2 0002
 * Time: 11:59
 */

namespace PhpComLab\Annotations\Node;

/**
 * Class Tag
 * @package PhpComLab\Annotations\Node
 */
class Tag
{
    /**
     * @var string Tag name
     */
    public $name;

    /**
     * @var string description text
     */
    public $description;

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
