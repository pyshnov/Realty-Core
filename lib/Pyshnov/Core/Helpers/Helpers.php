<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Helpers;

class Helpers
{
    /**
     * Формирование строки на основании числа.
     * Каждая из форм является sprintf шаблоном, с подстановкой первого параметра - числа
     *
     * @param $number
     * @param $one
     * @param $two
     * @param $five
     * @return string
     */
    public static function Plural($number, $one, $two, $five)
    {
        $form = $number % 10 == 1 && $number % 100 != 11
            ? $one
            : ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20)
                ? $two
                : $five);
        return sprintf($form, $number);
    }

    /**
     * Форматирует дату
     *
     * @param $string
     * @return string
     */
    public static function dateFormat($string)
    {
        $time = time();

        $monthList = [
            '01' => 'января',
            '02' => 'февраля',
            '03' => 'марта',
            '04' => 'апреля',
            '05' => 'мая',
            '06' => 'июня',
            '07' => 'июля',
            '08' => 'августа',
            '09' => 'сентября',
            '10' => 'октября',
            '11' => 'ноября',
            '12' => 'декабря'
        ];

        $unix_date_add = $unix_db_date = strtotime($string);

        $difference_seconds = $time - $unix_date_add;
        $difference_minutes = floor($difference_seconds / 60);
        $day_start = strtotime('now 00:00:00');

        if($difference_seconds < 10) {
            return ' только что';
        } elseif($difference_seconds < 60) {
            return $difference_seconds . ' ' . self::Plural($difference_seconds, 'секунда', 'секунды', 'секунд') . ' назад';
        } elseif($difference_minutes < 60) {
            return $difference_minutes . ' ' . self::Plural($difference_minutes, 'минута', 'минуты', 'минут') . ' назад';
        } elseif($difference_seconds < ($time - $day_start)) {
            return 'сегодня в ' . date('H:i', $unix_date_add);
        } elseif($difference_seconds < $time - ($day_start - 86400)) {
            return 'вчера в ' . date('H:i', $unix_date_add);
        } else {
            if(date('Y', $unix_date_add) == date('Y')) {
                return date('d', $unix_date_add) . ' ' . $monthList[date('m', $unix_date_add)];
            } else {
                return date('d', $unix_date_add) . ' ' . $monthList[date('m', $unix_date_add)] . ' ' . date('Y', $unix_date_add);
            }
        }
    }

    /**
     * Отрезает часть строки дополняя ее многоточием.
     * @param string $text входящая строка
     * @param int $start
     * @param int $length количество символов
     * @param $charset
     * @return string
     */
    public static function textTrim($text, $start = 0, $length = 240, $charset = 'utf-8')
    {
        $text = strip_tags($text);
        $more = '...';
        if(strlen($text) <= $length)
            $more = '';

        if(strtolower($charset) == 'utf-8')
            return mb_substr($text, $start, $length, 'utf-8') . $more;

        return substr($text, $start, $length) . $more;

    }

    public static function translit($str)
    {
        $str = (string)$str; // преобразуем в строковое значение
        $str = strip_tags($str); // убираем HTML-теги
        $str = str_replace(array("\n", "\r"), " ", $str); // убираем перевод каретки
        $str = preg_replace("/\s+/u", ' ', $str); // удаляем повторяющие пробелы
        $str = trim($str); // убираем пробелы в начале и конце строки
        $str = function_exists('mb_strtolower') ? mb_strtolower($str) : strtolower($str); // переводим строку в нижний регистр (иногда надо задать локаль)
        $str = strtr($str, [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shh',
            'ы' => 'y',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            'ъ' => '',
            'ь' => ''
        ]);
        $str = preg_replace("/[^0-9a-z-_ ]/i", "", $str); // очищаем строку от недопустимых символов
        $str = str_replace(" ", "-", $str); // заменяем пробелы знаком минус
        return $str; // возвращаем результат
    }
}