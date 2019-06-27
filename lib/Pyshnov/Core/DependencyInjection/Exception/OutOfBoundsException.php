<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\DependencyInjection\Exception;


use Psr\Container\ContainerExceptionInterface;

class OutOfBoundsException extends  \OutOfBoundsException implements ContainerExceptionInterface
{
    //Создается исключение, если значение не является действительным ключом. Это соответствует ошибкам, которые не могут быть обнаружены во время компиляции.
}