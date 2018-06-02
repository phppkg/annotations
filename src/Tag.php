<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/2 0002
 * Time: 10:59
 */

namespace Ulue\Annotations;

/**
 * Class Tag
 * @package Ulue\Annotations
 */
class Tag implements \ArrayAccess
{
    /**
     * @var string Tag name
     */
    public $name;

    /**
     * @var array Tag data
     */
    public $data;

    /**
     * @var Tag
     */
    public $parent;

    /**
     * @var Tag[]
     */
    public $children = [];

    /**
     * Tag constructor.
     * @param string $name
     * @param array $data
     * @param Tag[] $children
     * @param Tag|null $parent
     */
    public function __construct(string $name, array $data, array $children = [], Tag $parent = null)
    {
        $this->name = $name;
        $this->data = $data;
        $this->parent = $parent;
        $this->children = $children;
    }

    public function __toString(): string
    {
        return '';
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset): bool
    {
        return \property_exists($this, $offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->$offset;
        }

        return null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            $this->$offset = $value;
        }
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        //
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
