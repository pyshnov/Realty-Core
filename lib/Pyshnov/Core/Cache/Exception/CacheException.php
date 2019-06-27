<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Cache\Exception;


use Pyshnov\Core\Exception\Exception;

class CacheException extends Exception
{
    const FILE_NOT_WRITABLE = 1;

    protected static $errors_arr = [
        self::FILE_NOT_WRITABLE => 'Файл кэша недоступен для записи!'
    ];
}