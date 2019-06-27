<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Module;


use Pyshnov\Core\Extension\Extension;

interface ModuleHandlerInterface
{
    /**
     * @param Extension $module
     */
    public function addModule(Extension $module);

    /**
     * @return array
     */
    public function getModules();

    /**
     * @param string $name
     * @return array|string|bool
     */
    public function getPathName($name = 'all');

    /**
     * @param string $name
     * @return bool
     */
    public function hasModule($name);

    /**
     * @param string $name
     * @return bool
     */
    public function isEnable($name);

    /**
     * @param $name
     * @return Extension|bool
     */
    public function getModule($name);
}