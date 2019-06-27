<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Module;

use Pyshnov\Core\DB\DB;
use Pyshnov\Core\Extension\Extension;
use Pyshnov\Core\Extension\InfoParser;

class ModuleHandler implements ModuleHandlerInterface
{

    protected $rootDir;
    protected $modules;
    protected $notInstalled;
    protected $infoParser;

    public function __construct($root, InfoParser $info_parser, $modules = null)
    {
        $this->rootDir = $root;
        $this->infoParser = $info_parser;
        if (null !== $modules) {
            $this->setModules($modules);
        }
    }

    /**
     * @param array $modules
     */
    public function setModules(array $modules)
    {
        $mod = [];

        $stmt = DB::select('*', DB_PREFIX . '_modules')->execute()->fetchAll();

        foreach ($stmt as $item) {
            $mod[$item['name']] = $item['active'];
        }

        foreach ($modules as $name => $module) {
            $info = $this->infoParser->parse($this->rootDir . '/' . $module->getPathname(). '/info.yml');
            unset($info['type']);
            $info['machine_name'] = $module->getName();
            $info['dependence'] = $info['dependence'] ?? [];
            $info['required'] = $info['required'] ?? false;
            if(array_key_exists($name, $mod)) {
                $info['active'] = $mod[$name];
                $module->setInfo($info);
                $this->modules[$name] = $module;
            } else {
                $module->setInfo($info);
                $this->notInstalled[$name] = $module;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addModule(Extension $module)
    {
        $this->modules[$module->getName()] = $module;
    }

    /**
     * {@inheritdoc}
     */
    public function getModules():array
    {
        return $this->modules ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getModule($name)
    {
        return $this->modules[$name] ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasModule($name):bool
    {
        return isset($this->modules[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnable($name):bool
    {
        return $this->hasModule($name) ? $this->modules[$name]->getInfo()['active'] : false;
    }

    /**
     * @return array
     */
    public function getNotInstalled():array
    {
        return $this->notInstalled ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPathName($name = 'all')
    {
        if ($name == 'all') {
            $res = [];
            foreach ($this->getModules() as $name => $module) {
                $res[$name] = $module->getPathname();
            }
        } else {
            $res = $this->hasModule($name) ? $this->getModule($name)->getPathname() : false;
        }

        return $res;
    }


}