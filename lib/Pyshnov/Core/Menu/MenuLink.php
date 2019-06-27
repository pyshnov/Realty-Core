<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Menu;


use Pyshnov\Core\Module\ModuleHandler;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class MenuLink
{

    /**
     * @var \Pyshnov\Core\Module\ModuleHandler
     */
    protected $moduleHandler;

    protected $rootDir;
    protected $links;

    protected $yamlParser;

    public function __construct($root, ModuleHandler $module_handler)
    {
        $this->rootDir = $root;
        $this->moduleHandler = $module_handler;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        if (null === $this->links) {

            $arr = [];

            // Получаем содержимое всех файлов и записываем в единый массив
            foreach ($this->moduleHandler->getModules() as $n => $mod) {
                $file = $this->rootDir . '/' . $mod->getPathname() . '/links.menu.yml';

                if (file_exists($file) && isset($mod->info['active'])) {
                    if (null !== $yml = $this->parseLinks($file))
                        $arr += $yml;
                }
            }

            $file = $this->rootDir . '/' . \Pyshnov::config()->get('theme_pathname') . '/links.menu.yml';

            if (file_exists($file)) {
                if (null !== $yml = $this->parseLinks($file))
                    $arr += $yml;
            }

            uasort($arr, function($a, $b) {

                if (!isset($a['cort']))
                    return 1;

                if (!isset($b['cort']))
                    return -1;

                return $a['cort'] <=> $b['cort'];

            });

            // TODO нужно подусать над рекурсивной функцией на случай если потребуется ьольше уровней вложенности

            // Перебираем массив и отбираем все элемены верхнего уровня
            // и удаляем их из общего массива, оставив только второй уровень
            foreach ($arr as $name => $value) {
                if (!isset($value['parent'])) {
                    $this->links[$value['menu_name']][$name] = new Link($value);
                    unset($arr[$name]);
                }
            }

            // Перебирвем оставшиеся элементы
            foreach ($arr as $name => $value) {
                $this->links[$value['menu_name']][$value['parent']]->setBelow(new Link($arr[$name]));
            }
        }

        return $this->links;
    }

    /**
     * @return array
     */
    public function build()
    {
        $items = [];

        $url_path = \Pyshnov::routeMatch()->getRoute()->getPath();

        foreach ($this->getLinks() as $key => $item) {
            $items[$key] = $this->buildItems($item, $url_path);
        }

        return $items;
    }

    /**
     * @param $links
     * @param $path
     * @return array
     */
    protected function buildItems($links, $path)
    {
        $items = [];

        foreach ($links as $name => $link) {
            $element = [];

            $element['enabled'] = $link->isEnabled();
            $element['title'] = $link->getTitle();
            $element['url'] = $link->getUrl();
            $element['below'] = $link->getBelow() ? $this->buildItems($link->getBelow(), $path) : false;

            if ($element['url'] == $path || $element['url'] == \Pyshnov::request()->getPathInfo())
                $element['attributes'] = ' class="active"';

            $items[] = $element;
        }

        return $items;
    }

    /**
     * @param $file
     * @return mixed
     */
    public function parseLinks($file)
    {
        if (null === $this->yamlParser) {
            $this->yamlParser = new Parser();
        }

        return $this->yamlParser->parse(file_get_contents($file), Yaml::PARSE_CONSTANT);

    }

}