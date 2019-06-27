<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Extension;


class Extension
{
    protected $rootDir;
    protected $type;
    protected $pathName;
    protected $file;
    protected $origin;
    public $info;

    public function __construct($root, $type, $path_name, $file, $origin)
    {
        $this->rootDir = $root;
        $this->type = $type;
        $this->pathName = $path_name;
        $this->file = $file;
        $this->origin = $origin;
        $this->info = [];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return basename($this->pathName);
    }

    /**
     * Возвращает относительный путь до расширению.
     *
     * @return string
     */
    public function getPath(): string
    {
        return dirname($this->pathName);
    }

    /**
     * Возвращает относительный путь до расширению + само название.
     *
     * @return string
     */
    public function getPathname(): string
    {
        return $this->pathName;
    }

    /**
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * @return array
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * @param array $info
     */
    public function setInfo(array $info)
    {
        $this->info = $info;
    }

    /**
     * @param $key
     * @param $value
     */
    public function addInfo($key, $value)
    {
        $this->info[$key] = $value;
    }

    public function load() {
        if (null !== $this->file) {
            include_once $this->rootDir. '/' . $this->file;
            return true;
        }
        return false;
    }


}