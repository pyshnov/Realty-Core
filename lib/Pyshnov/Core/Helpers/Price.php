<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Helpers;


class Price
{
    /**
     * Форматирует цену в читаемый вид
     *
     * @param      $str
     * @param null $type
     * @return float|string
     */
    public static function priceFormat($str, $type = null)
    {
        if ($type) {
            $priceFormat = $type;
        } else {
            $priceFormat = \Pyshnov::config()->get('price_format');
        }

        //без форматирования
        if ($priceFormat == '1234.56') {
            $result = $str;
        } else

            //разделять тысячи пробелами, а копейки запятыми
            if ($priceFormat === '1 234,56') {
                $result = number_format($str, 2, ',', ' ');
            } else

                //разделять тысячи запятыми, а копейки точками
                if ($priceFormat === '1,234.56') {
                    $result = number_format($str, 2, '.', ',');
                } else

                    //без копеек, без форматирования
                    if ($priceFormat == '1234') {
                        $result = round($str);
                    } else

                        //без копеек, разделять тысячи пробелами, а копейки запятыми
                        if ($priceFormat == '1 234') {
                            $result = number_format(round($str), 0, ',', ' ');
                        } else

                            //без копеек, разделять тысячи запятыми, а копейки точками
                            if ($priceFormat == '1,234') {
                                $result = number_format(round($str), 0, '.', ',');
                            } else {
                                $result = number_format(round($str), 0, ',', ' ');
                            }

        $cent = substr($result, -3);

        if ($cent === '.00' || $cent === ',00') {
            $result = substr($result, 0, -3);
        }

        return $result;
    }

    /**
     * Деформатирует цену в читаемый вид. Убирает пробелы и заяпятые.
     *
     * @param $str
     * @return mixed|string
     */
    public static function priceDeFormat($str)
    {

        $result = $str;

        $cent = false;
        $thousand = false;

        $existpoint = strrpos($str, '.');
        $existcomma = strrpos($str, ',');

        // 1,320.50
        if ($existpoint && $existcomma) {
            $result = str_replace(' ', '', $str);
            $result = str_replace(',', '.', $result);
            $firstpoint = stripos($result, '.');
            $lastpoint = strrpos($result, '.');
            if ($firstpoint != $lastpoint) {
                $str1 = substr($result, 0, $lastpoint);
                $str2 = substr($result, $lastpoint);
                $str1 = str_replace('.', '', $str1);
                $result = $str1 . $str2;
            }

            return $result;
        }

        // 1,234 или 1 234,56
        if (!$existpoint && $existcomma) {
            //определяем, что отделяется запятой, тысячи или копейки
            $str2 = substr($str, $existcomma);
            if (strlen($str2) - 1 == 2) {
                $cent = true;
            } else {
                $thousand = true;
            }
        }

        if ($thousand) {
            $result = str_replace(',', '', $str);
        }

        if ($cent) {
            $result = str_replace(',', '.', $str);
            $firstpoint = stripos($result, '.');
            $lastpoint = strrpos($result, '.');
            if ($firstpoint != $lastpoint) {
                $str1 = substr($result, 0, $lastpoint);
                $str2 = substr($result, $lastpoint);
                $str1 = str_replace('.', '', $str1);
                $result = $str1 . $str2;
            }
        }

        $result = str_replace(' ', '', $result);

        return $result;
    }

    /**
     * Форматирует цену в читаемый вид
     *
     * @param string  $price - цена
     * @param boolean $format - нужно форматировать или нет
     * @param boolean $useFloat - округлять до целых
     * @return string - форматированная строка с ценой.
     */
    public static function priceCourse($price, $format = true, $useFloat = null)
    {

        if ($useFloat === false) {
            $price = round($price);
        }

        if ($format) {
            $price = self::priceFormat($price);
        }

        return $price;
    }
}