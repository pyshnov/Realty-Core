<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\DependencyInjection\ParameterBag;


class ParameterBag implements ParameterBagInterface
{
    private $parameters;
    private $resolved;

    public function __construct(array $parameters = [])
    {
        $this->set($parameters);
    }

    public function get($name, $default = null)
    {
        return $this->has($name) ? $this->parameters[strtolower($name)] : $default;
    }

    public function set(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->parameters[strtolower($key)] = $value;
        }
    }

    public function add($name, $value)
    {
        $this->parameters[strtolower($name)] = $value;
    }

    public function has($name)
    {
        return array_key_exists(strtolower($name), $this->parameters);
    }

    public function remove($name)
    {
        unset($this->parameters[strtolower($name)]);
    }

    public function clear()
    {
        $this->parameters = [];
    }

    public function all()
    {
        return $this->parameters;
    }

    public function resolve()
    {
        if ($this->resolved) {
            return;
        }

        $parameters = [];
        foreach ($this->parameters as $key => $value) {
            try {
                $value = $this->resolveValue($value);
                $parameters[$key] = $value;
            } catch (\Exception $e) {
                throw $e;
            }
        }

        $this->parameters = $parameters;
        $this->resolved = true;
    }

    /**
     * @param       $value
     * @param array $resolving
     * @return array|mixed|null
     */
    public function resolveValue($value, array $resolving = [])
    {
        if (is_array($value)) {
            $args = [];
            foreach ($value as $k => $v) {
                $args[$this->resolveValue($k, $resolving)] = $this->resolveValue($v, $resolving);
            }

            return $args;
        }

        if (!is_string($value)) {
            return $value;
        }

        return $this->resolveString($value, $resolving);
    }

    /**
     * @param       $value
     * @param array $resolving
     * @return array|mixed|null
     */
    public function resolveString($value, array $resolving = [])
    {
        if (preg_match('/^%([^%\s]+)%$/', $value, $match)) {
            $key = $match[1];

            $resolving[strtolower($key)] = true;

            return $this->resolved ? $this->get($key) : $this->resolveValue($this->get($key), $resolving);
        }

        return preg_replace_callback('/%%|%([^%\s]+)%/', function($match) use ($resolving, $value) {

            $key = $match[1];

            $resolved = $this->get($key);

            $resolved = (string)$resolved;
            $resolving[strtolower($key)] = true;

            return $this->isResolved() ? $resolved : $this->resolveString($resolved, $resolving);
        }, $value);
    }

    public function isResolved()
    {
        return $this->resolved;
    }
}