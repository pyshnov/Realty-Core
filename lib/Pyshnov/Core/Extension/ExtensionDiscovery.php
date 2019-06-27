<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Extension;


use Pyshnov\Core\Extension\Discovery\ExtensionRecursiveFilterIterator;

class ExtensionDiscovery
{
    protected $rootDir;
    protected $ignoreDirectories;

    protected static $files = [];

    public function __construct($root, $ignore_directories = [])
    {
        $this->rootDir = $root;
        $this->ignoreDirectories = $ignore_directories;
    }

    public function scan($type)
    {

        $files = [];

        foreach (['core', ''] as $dir) {
            // Если запрашивается впервые, сканируем все каталоги
            if (!isset(static::$files[$dir])) {
                static::$files[$dir] = $this->scanDirectory($dir);
            }
            // Отбираем только запрошенный тип
            if (isset(static::$files[$dir][$type])) {
                $files += static::$files[$dir][$type];
            }
        }

        return $files;

    }

    protected function scanDirectory($dir)
    {
        $files = [];

         // Чтобы сканировать каталоги верхнего уровня,
         // необходимо использовать абсолютные пути к каталогам
         // (что также повышает производительность, поскольку с помощью настраиваемых PHP include_paths не будут проводиться консультации).
         // Сохраните относительный исходный каталог,
         // который будет сканироваться, поэтому относительные пути могут быть восстановлены ниже
         // (ожидается, что все пути будут относиться к $ this->root).
        $dir_prefix = $dir == '' ? '' : $dir . '/';
        $absolute_dir = $dir == '' ? $this->rootDir : $this->rootDir . '/' . $dir;

        if (!is_dir($absolute_dir)) {
            return $files;
        }

        // Использовать пути Unix независимо от платформы,
        // пропускать точечные каталоги, следовать символическим ссылкам
        // (чтобы позволить связывать расширения из других источников)
        // и возвращать экземпляр RecursiveDirectoryIterator,
        // чтобы иметь доступ к getSubPath (),
        // поскольку SplFileInfo не поддерживает относительные пути.

        $flags = \FilesystemIterator::UNIX_PATHS;
        $flags |= \FilesystemIterator::SKIP_DOTS;
        $flags |= \FilesystemIterator::FOLLOW_SYMLINKS;
        $flags |= \FilesystemIterator::CURRENT_AS_SELF;
        $directory_iterator = new \RecursiveDirectoryIterator($absolute_dir, $flags);


        // Filter the recursive scan to discover extensions only.
        // Important: Without a RecursiveFilterIterator, RecursiveDirectoryIterator
        // would recurse into the entire filesystem directory tree without any kind
        // of limitations.
        $filter = new ExtensionRecursiveFilterIterator($directory_iterator, $this->ignoreDirectories);

        // Рекурсивное сканирование файловой системы вызывается только путем создания экземпляра
        // RecursiveIteratorIterator.
        $iterator = new \RecursiveIteratorIterator($filter,
            \RecursiveIteratorIterator::LEAVES_ONLY,
            // Подавлять ошибки файловой системы в случае, если доступ к каталогу невозможен.
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        foreach ($iterator as $key => $file_info) {

            $type = false;
            $file = $file_info->openFile('r');
            while (!$type && !$file->eof()) {
                $res = $file->fgets();
                preg_match('@^type:\s*(\'|")?(\w+)\1?\s*$@', $res, $matches);
                if (isset($matches[2])) {
                    $type = $matches[2];
                }
            }

            if (!$type) {
                continue;
            }

            $path_name = dirname($dir_prefix . $file_info->getSubPathname());

            $file_name = $path_name . '/' . $type . '.php';

            if (!file_exists($this->rootDir . '/' . $file_name)) {
                $file_name = null;
            }

            $extension = new Extension($this->rootDir, $type, $path_name, $file_name, $dir);

            $files[$type][basename($path_name)] = $extension;

        }

        return $files;
    }
}