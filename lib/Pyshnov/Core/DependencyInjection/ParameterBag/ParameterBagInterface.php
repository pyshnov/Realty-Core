<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\DependencyInjection\ParameterBag;


interface ParameterBagInterface
{

    public function clear();

    public function add($name, $value);

    public function all();

    public function get($name);

    public function remove($name);

    public function set(array $parameters);

    public function has($name);

    public function resolve();

    public function resolveValue($value, array $resolving = []);

    public function resolveString($value, array $resolving = []);

}