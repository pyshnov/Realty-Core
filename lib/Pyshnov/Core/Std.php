<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core;


class Std
{
    protected $__data = [];

    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->fromArray($data);
        }
    }

    /**
     * @param array $data
     * @return Std $this
     */
    public function fromArray($data)
    {
        $data = (array)$data;

        foreach ($data as $key => $value) {
            if (is_null($value) || is_scalar($value) || $value instanceof \Closure) {
                $this->__data[$key] = $value;
            } else {
                $this->__data[$key] = new self;
                $this->{$key}->fromArray($value);
            }
        }

        return $this;
    }

    /**
     * Вернет массив свойств
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach (array_keys($this->__data) as $key) {
            $value = $this->get($key);
            if ($value instanceof self) {
                $data[$key] = $value->toArray();
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Вернет весь массив
     *
     * @return array
     */
    public function getData()
    {
        return $this->__data;
    }

    /**
     * Проверит присутствует ли в $this->__data ключ или индекс
     *
     * @param $key
     * @return bool
     */
    protected function has($key)
    {
        return array_key_exists($key, $this->__data);
    }

    /**
     * Удалит из $this->__data ключ
     *
     * @param $key
     */
    protected function unset($key)
    {
        unset($this->__data[$key]);
    }

    /**
     * @param      $key
     * @param null $default
     * @return mixed|null
     */
    protected function get($key, $default = null)
    {
        return $this->__data[$key] ?? $default;
    }

    /**
     * @param $data
     */
    public function set($data)
    {
        $this->fromArray($data);
    }

    /**
     * @param $key
     * @param $val
     */
    protected function add($key, $val)
    {
        $this->__data[$key] = $val;

    }

    /**
     * \Iterator implementation
     */
    public function current()
    {
        return key($this->__data);
    }

    public function next()
    {
        next($this->__data);
    }

    public function key()
    {
        return key($this->__data);
    }

    public function valid()
    {
        return null !== key($this->__data);
    }

    public function rewind()
    {
        reset($this->__data);
    }

    public function count()
    {
        return count($this->__data);
    }

    /*
     * "Magic" methods
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    public function __unset($key)
    {
        $this->unset($key);
    }

    public function __get($key)
    {
        if (!$this->has($key))
            return null;

        return $this->get($key);
    }

    public function __set($key, $val)
    {
        $this->add($key, $val);
    }
}