<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\form\Helpers;

class Filter
{
    /**
     * Фильтрация значений.
     * Возвращает значение, если оно проходит фильтр или $default
     * $filter - callable, regexp или список допустимых значений
     * Значение в фильтр передается по ссылке!
     *
     * @param          $val
     * @param callable $filter
     * @param bool     $default
     * @return bool
     */
    public static function value(&$val, $filter = null, $default = false)
    {
        $ok = true;
        if (is_callable($filter)) {
            $ok = call_user_func_array($filter, array(&$val));
        } elseif (is_array($filter)) {
            $ok = in_array($val, $filter);
        } elseif (!empty($filter)) {
            $ok = preg_match($filter, $val);
        }

        return $ok ? $val : $default;
    }

    /**
     * Фильтрация значений массива. Возвращает результирующий массив.
     * Оставляет в результирующем массиве только допустимые значения, или $default, если допустимых значений нет.
     * Callable может принимать значение по ссылке и изменять его для выводного массива
     *
     * @param       $array
     * @param null  $filter
     * @param array $default
     * @return array|bool
     */
    public static function Arr($array, $filter = null, $default = array())
    {
        if (empty($array) || !is_array($array)) {
            return $default;
        }
        $out = false;
        foreach ($array as $key => $val) {
            if (!is_null(static::Value($val, $filter, null))) {
                $out[$key] = $val;
            }
        }

        return $out ?: $default;
    }
}