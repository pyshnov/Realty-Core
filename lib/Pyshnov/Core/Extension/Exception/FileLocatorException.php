<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Extension\Exception;



use Pyshnov\Core\Exception\Exception;

class FileLocatorException extends Exception
{
    const FILE_NOT_EXIST = 1;
    const FILE_NOT_EXIST_IN = 2;

    protected static $errors_arr = [
        self::FILE_NOT_EXIST => 'Файл "%s" не найден.',
        self::FILE_NOT_EXIST_IN => 'Файл "%s" не найден (в "%s").'
    ];
}