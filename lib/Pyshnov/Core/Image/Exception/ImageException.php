<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

/**
 * Created by PhpStorm.
 * User: aleksandr
 * Date: 08.05.17
 * Time: 12:40
 */

namespace Pyshnov\Core\Image\Exception;


use Pyshnov\Core\Exception\Exception;

class ImageException extends Exception
{
    const FILE_NOT_EXIST = 1;
    const BAD_TYPE = 2;
    const RESIZE = 10;
    const CROP = 11;
    const WATERMARK = 12;
    const FILE_BIGGER = 13;
    const NOT_RIGHT_EXTENSION = 14;
    protected static $errors_arr = [
        self::FILE_NOT_EXIST => 'Изображение "%s" не существует',
        self::BAD_TYPE => 'Неверный тип изображения',
        self::RESIZE => 'Ошибка при изменении размера изображения',
        self::CROP => 'Ошибка при обрезке изображения',
        self::FILE_BIGGER => 'Файл больше "%s" мб',
        self::NOT_RIGHT_EXTENSION => 'Не верный формат "%s"',
    ];
}