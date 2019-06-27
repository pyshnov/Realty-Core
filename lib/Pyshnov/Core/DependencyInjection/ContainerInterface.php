<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\DependencyInjection;

use Pyshnov\Core\DependencyInjection\ParameterBag\ParameterBagInterface;

interface ContainerInterface
{

    /**
     * @return ParameterBagInterface
     */
    public function getParameterBag();

    public function getParameter($name, $default = null);

    public function hasParameter($name);

    public function addParameter($name, $value);

    public function setDefinition($id, Definition $definition);

    public function hasDefinition($id);

    public function getDefinition($id);

    public function getDefinitions();

    public function setAliases(array $aliases);

    public function addAlias($alias, $id);

    public function removeAlias($alias);

    public function hasAlias($alias);

    public function getAliases();

    public function getAlias($alias);

    public function set($id, $service);

    public function has($id);

    public function get($id);

}