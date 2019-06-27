<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\DependencyInjection\Exception;


use Psr\Container\ContainerExceptionInterface;
use Pyshnov\Core\Exception\Exception;

class ContainerException extends Exception implements ContainerExceptionInterface
{
    const SERVICE_COMPILED = 1;

    protected static $errors_arr = [
        self::SERVICE_COMPILED => 'Установка сервиса "%s" невозиожна, контейнер уже скомпилированн.',
    ];
}