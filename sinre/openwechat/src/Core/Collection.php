<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/25
 * Time: 15:55
 */
namespace OpenWechat\Core;

use OpenWechat\Core\Helper\ArrayHelper;

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable, \Serializable
{
    /**
     * The collection data.
     *
     * @var array
     */
    protected $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * 实现ArrayAccess接口方法
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * 实现ArrayAccess接口方法
     * @param mixed $offset
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->get($offset) : null;
    }

    /**
     * 实现ArrayAccess接口方法
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * 实现ArrayAccess接口方法
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if($this->offsetExists($offset)){
            $this->forget($offset);
        }
    }

    /**
     * 实现Countable接口方法
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * 实现IteratorAggregate接口方法
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * 实现JsonSerialize接口方法
     */
    public function jsonSerialize()
    {
        return $this->items;
    }

    /**
     * 实现Serializable接口方法
     */
    public function serialize()
    {
        return serialize($this->items);
    }

    /**
     * 实现Serializable接口方法
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        return $this->items = unserialize($serialized);
    }

    public function set($key, $value)
    {
        ArrayHelper::set($this->items, $key, $value);
    }

    public function get($key, $default = null)
    {
        return ArrayHelper::get($this->items, $key, $default);
    }

    public function has($key)
    {
        return !is_null(ArrayHelper::get($this->items, $key));
    }

    public function forget($key)
    {
        ArrayHelper::forget($this->items, $key);
    }

    public function getAll()
    {
        return $this->items;
    }

    /**
     * 获取指定键数组
     * @param array $keys
     * @return array
     */
    public function only(array $keys = [])
    {
        $result = [];
        if(!$keys || !$this->items) return $result;

        foreach($keys as $key){
            $value = $this->get($key);
            if(!is_null($value)){
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 获取不包含指定键数组
     * @param array $keys
     */
    public function except(array $keys = [])
    {
        $result = [];
        if(!$keys || !$this->items) return $result;

        $result = ArrayHelper::except($this->items, $keys);
        return new static($result);
    }

    public function merge(array $data = [])
    {
        foreach($data as $key => $val){
            $this->set($key, $val);
        }
        return $this->getAll();
    }

    public function first()
    {
        return reset($this->items);
    }

    public function last()
    {
        $end = end($this->items);
        reset($this->items);
        return $end;
    }

    public function toArray()
    {
        return $this->getAll();
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function toJson($option = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->getAll(), $option);
    }
    /**
     * Get a data by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Assigns a value to the specified data.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Whether or not an data exists by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * Unsets an data by key.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        $this->forget($key);
    }

    /**
     * var_export.
     *
     * @return array
     */
    public function __set_state()
    {
        return $this->all();
    }
}