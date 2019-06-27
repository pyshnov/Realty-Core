<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Helpers;


class Directory
{
    /**
     * Возвращает список директорий
     *
     * @param string $dir
     * @param bool   $asc_sort Сортировать по алфавиту, по умолчанию false
     * @return array
     */
    public static function getDirsList($dir, $asc_sort = false)
    {
        if (!static::isAbsolutePath($dir)) {
            $dir = \Pyshnov::root() . DIRECTORY_SEPARATOR . $dir;
        }

        if (!is_dir($dir)) {
            return [];
        }

        $dir_context = opendir($dir);

        $list = [];

        while ($next = readdir($dir_context)) {

            if (in_array($next, ['.', '..'])) {
                continue;
            }
            if (strpos($next, '.') === 0) {
                continue;
            }
            if (!is_dir($dir . '/' . $next)) {
                continue;
            }

            $list[] = $next;
        }

        if ($asc_sort) {
            asort($list);
        }

        return $list;
    }

    /**
     * Проверит является ли абсолютным путь
     *
     * @param $file
     * @return bool
     */
    public static function isAbsolutePath($file)
    {
        if ($file[0] === '/' || $file[0] === '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] === ':'
                && ($file[2] === '\\' || $file[2] === '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ) {
            return true;
        }

        return false;
    }
}