<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Exception;


class Exception extends \Exception
{
    protected static $errors_arr = [];

    /**
     * Получение названия ошибки по коду
     *
     * @param $code
     * @param null $args
     * @param null $_
     * @return bool|mixed
     */
    public static function GetNameForCode($code, $args = null, $_ = null)
    {
        if (!array_key_exists($code, static::$errors_arr)) {
            return false;
        }
        $str = static::$errors_arr[$code];
        if (!is_null($args)) {
            if (!is_array($args)) {
                $args = array_slice(func_get_args(), 1);
            }
            array_unshift($args, $str);
            return call_user_func_array('sprintf', $args);
        }
        return $str;
    }

    /**
     * Создание исключения с текстом ошибки
     *
     * @param $code
     * @param null $args
     * @param null $_
     * @return static
     */
    public static function Create($code, $args = null, $_ = null)
    {
        if (!is_null($args)) {
            if (!is_array($args)) {
                $args = array_slice(func_get_args(), 1);
            }
        }
        $str = static::GetNameForCode($code, $args);
        return new static($str, $code);
    }

    /**
     * Выброс исключения с текстом ошибки
     *
     * @param $code
     * @param null $args
     * @param null $_
     * @throws Exception
     */
    public static function ThrowError($code, $args = null, $_ = null)
    {
        if (!is_null($args)) {
            if (!is_array($args)) {
                $args = array_slice(func_get_args(), 1);
            }
        }
        throw static::Create($code, $args);
    }
}