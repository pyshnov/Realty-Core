<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Config;


interface ConfigInterface
{
    /**
     * @param $key
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     * @param null   $default
     * @return null
     */
    public function get(string $key, $default = null);

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value);
}