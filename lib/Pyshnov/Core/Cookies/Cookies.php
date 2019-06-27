<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Cookies;


use Symfony\Component\HttpFoundation\Cookie;

class Cookies extends Cookie
{
    public function send()
    {
        if ($this->isRaw()) {
            setrawcookie($this->getName(), $this->getValue(), $this->getExpiresTime(), $this->getPath(), $this->getDomain(), $this->isSecure(), $this->isHttpOnly());
        } else {
            setcookie($this->getName(), $this->getValue(), $this->getExpiresTime(), $this->getPath(), $this->getDomain(), $this->isSecure(), $this->isHttpOnly());
        }
    }

    public function delete()
    {
        $this->send();
    }

    public static function has($name)
    {
        return isset($_COOKIE[$name]);
    }

    public static function get($name, $default = null)
    {
        if (static::has($name)) {
            return $_COOKIE[$name];
        }
        return $default;
    }

}