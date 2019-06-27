<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Cache\FileCache;


use Symfony\Component\Filesystem\Filesystem;

class FileCache
{
    private $cacheDir;
    private $cacheTime;

    function __construct($cache_dir = null, $cache_time = 3600)
    {
        $cache_dir = $cache_dir ??
            \Pyshnov::service('kernel')->getCacheDir();
        $this->cacheDir = rtrim($cache_dir, '/') . '/';
        $this->cacheTime = $cache_time;
    }

    /**
     * @return int
     */
    public function getCacheTime(): int
    {
        return $this->cacheTime;
    }

    /**
     * @param int $cacheTime
     */
    public function setCacheTime(int $cacheTime)
    {
        $this->cacheTime = $cacheTime;
    }

    /**
     * Сохраняет кеш
     *
     * @param string $key
     * @param $value
     * @return bool
     */
    public function save(string $key, $value)
    {
        $cache_file = $this->getCacheFile($key);

        $mode = 0666;
        $umask = umask();
        $filesystem = new Filesystem();
        $filesystem->dumpFile($cache_file, serialize($value));

        $filesystem->chmod($cache_file, $mode, $umask);

        return true;
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function get(string $key)
    {
        $cache_file = $this->getCacheFile($key);
        if(!file_exists($cache_file)) {
            return false;
        }
        if(time() - $this->cacheTime > filemtime($cache_file)) {
            @unlink($cache_file);
            return false;
        }
        return unserialize(file_get_contents($cache_file));
    }

    /**
     * Удалить значение из кэша
     *
     * @param string $key
     */
    public function remove(string $key)
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->getCacheFile($key));
    }

    /**
     * Удалит все просроченные записи из кэша
     *
     * @return bool
     */
    public function clearCache()
    {
        $cache_dir = $this->getCacheDir();

        if($handle = opendir($cache_dir)) {
            while(($file = readdir($handle)) !== false) {
                if($file != '.' && $file != '..'
                    && (time() - $this->cacheTime > filemtime($this->getCacheDir() . $file))
                ) {
                    @unlink($cache_dir. $file);
                }
            }
        }
        return true;
    }

    /**
     * Очистить весь кэш вместе с директорией
     */
    public function dropCache()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->getCacheDir());
    }

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    /**
     * @param string $file_name
     * @return string
     */
    private function getCacheFile(string $file_name)
    {
        $file_name = md5($file_name);
        return $this->getCacheDir() . $file_name . '.cache';
    }

}