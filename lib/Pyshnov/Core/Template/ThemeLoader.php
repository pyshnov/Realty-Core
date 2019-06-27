<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Template;


use Pyshnov\Core\Config\ConfigInterface;
use Pyshnov\Core\Module\ModuleHandlerInterface;

class ThemeLoader extends \Twig_Loader_Filesystem
{
    protected $rootDir;

    public function __construct($root, ModuleHandlerInterface $module_handler, ConfigInterface $config)
    {
        $this->rootDir = $root;

        $path = $root . '/'
            . $config->get('theme_pathname') . '/'
            . 'templates';

        parent::__construct($path, $root);

        $dir = new \DirectoryIterator($path);

        foreach ($dir as $file) {
            if ($file->isDir() && !$file->isDot()) {
                $this->prependPath($file->getPathname() );
            }
        }

        $this->addModulesTemplateDir($module_handler);

        $this->addPath($root . '/core/templates/engine/twig/templates');
    }

    /**
     * @param ModuleHandlerInterface $module_handler
     */
    public function addModulesTemplateDir($module_handler)
    {
        foreach ($module_handler->getPathName() as $name => $path_name) {

            $path = $this->rootDir . '/' . $path_name . '/templates';

            if(is_dir($path)) {
                $this->addPath($path);
            }
        }
    }
}