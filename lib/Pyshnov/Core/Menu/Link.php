<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Menu;


class Link {

    protected $linkDefinition;

    public function __construct($link_definition)
    {
        $this->linkDefinition = $link_definition;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->linkDefinition['enabled'];
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->linkDefinition['title'];
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->linkDefinition['route_name'] ?? '';
    }

    /**
     * Вернет ссылку
     *
     * @return mixed
     */
    public function getUrl()
    {
        if(isset($this->linkDefinition['url'])) {
            return $this->linkDefinition['url'];
        } else {
            if($route = \Pyshnov::service('router')->getCollection()->get($this->getRouteName())) {
                return $route->getPath();
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Не определен роутер "%s" запрошенный при генерации ссылки меню.', $this->getRouteName())
                );
            }
        }
    }

    /**
     * @param $below
     */
    public function setBelow($below)
    {
        $this->linkDefinition['below'][] = $below;
    }

    /**
     * @return bool
     */
    public function getBelow()
    {
        return $this->linkDefinition['below'] ?? false;
    }

}