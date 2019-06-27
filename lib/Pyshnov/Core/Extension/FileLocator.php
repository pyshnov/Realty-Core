<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Extension;



use Pyshnov\Core\Helpers\Directory;
use Pyshnov\Core\Extension\Exception\FileLocatorException;

class FileLocator
{
    protected $paths;

    public function __construct($paths = [])
    {
        $this->paths = (array)$paths;
    }

    /**
     * Возвращает полный путь для заданного имени файла.
     *
     * @param string      $name        Имя файла для поиска
     * @param string|null $currentPath Текущий путь
     * @param bool        $first       Возвращать ли первое вхождение или массив имен файлов
     *
     * @return string|array Полный путь к файлу или массив путей к файлу
     */
    public function locate($name, $currentPath = null, $first = true)
    {
        if (Directory::isAbsolutePath($name)) {
            if (!file_exists($name)) {
                FileLocatorException::ThrowError(FileLocatorException::FILE_NOT_EXIST, $name);
            }

            return $name;
        }

        $paths = $this->paths;

        if (null !== $currentPath) {
            array_unshift($paths, $currentPath);
        }

        $paths = array_unique($paths);
        $file_paths = [];

        foreach ($paths as $path) {
            if (@file_exists($file = $path.DIRECTORY_SEPARATOR.$name)) {
                if (true === $first) {
                    return $file;
                }
                $file_paths[] = $file;
            }
        }

        if (!$file_paths) {
            FileLocatorException::ThrowError(FileLocatorException::FILE_NOT_EXIST_IN, $name, implode(', ', $paths));
        }

        return $file_paths;
    }

}