<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\DependencyInjection;


use Pyshnov\Core\DependencyInjection\Exception\ContainerException;
use Pyshnov\Core\DependencyInjection\Exception\InvalidArgumentException;
use Pyshnov\Core\DependencyInjection\Exception\RuntimeException;
use Pyshnov\Core\DependencyInjection\Exception\ServiceNotFoundException;
use Pyshnov\Core\DependencyInjection\ParameterBag\ParameterBag;
use Pyshnov\Core\DependencyInjection\ParameterBag\ParameterBagInterface;

class Container implements ContainerInterface
{
    /**
     * @var ParameterBagInterface
     */
    protected $parameterBag;
    private $services;
    private $compiled;
    private $loading;

    /**
     * @var Alias[]
     */
    private $aliasDefinitions = [];

    /**
     * @var Definition[]
     */
    private $definitions = [];

    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag ?? new ParameterBag();
        $this->services = [];

        $this->compiled = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterBag()
    {
        return $this->parameterBag;
    }

    /**
     * @param      $name
     * @param null $default
     * @return null
     */
    public function getParameter($name, $default = null)
    {
        return $this->parameterBag->get($name, $default);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasParameter($name)
    {
        return $this->parameterBag->has($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function addParameter($name, $value)
    {
        $this->parameterBag->add($name, $value);
    }

    public function compile()
    {
        $this->parameterBag->resolve();
    }

    public function isCompiled()
    {
        return $this->compiled;
    }

    /**
     * @param            $id
     * @param Definition $definition
     * @return Definition
     */
    public function setDefinition($id, Definition $definition)
    {
        return $this->definitions[$id] = $definition;
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasDefinition($id)
    {
        return isset($this->definitions[$id]);
    }

    /**
     * @param $id
     * @return Definition
     */
    public function getDefinition($id)
    {
        if (!isset($this->definitions[$id])) {
            throw new ServiceNotFoundException($id);
        }

        return $this->definitions[$id];
    }

    /**
     * @return Definition[]
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    public function set($id, $service)
    {
        $id = strtolower($id);

        if ($this->isCompiled()) {
            ContainerException::ThrowError(ContainerException::SERVICE_COMPILED, $id);
        }

        unset($this->definitions[$id], $this->aliasDefinitions[$id]);

        $this->services[$id] = $service;

        if (null === $service) {
            unset($this->services[$id]);
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function has($id)
    {
        return isset($this->definitions[$id]) || isset($this->aliasDefinitions[$id]) || isset($this->services[$id]);
    }

    public function get($id)
    {
        $id = strtolower($id);

        if ('service_container' === $id) {
            return $this;
        }

        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        $definition = $this->getDefinition($id);

        $this->loading[$id] = true;

        try {
            $service = $this->createService($definition, $id);
        } finally {
            unset($this->loading[$id]);
        }

        return $service;
    }

    public function setAliases(array $aliases)
    {
        $this->aliasDefinitions = [];
        foreach ($aliases as $alias => $id) {
            $this->addAlias($alias, $id);
        }
    }

    public function addAlias($alias, $id)
    {
        if (is_string($id)) {
            $id = new Alias($id);
        } elseif (!$id instanceof Alias) {
            throw new InvalidArgumentException('$id должен быть строкой или объектом Alias.');
        }

        if ($alias === (string) $id) {
            throw new InvalidArgumentException(sprintf('Псевдоним не может ссылаться на себя, получил круговую ссылку на "%s".', $alias));
        }

        unset($this->definitions[$alias]);

        $this->aliasDefinitions[$alias] = $id;
    }

    /**
     * @param $alias
     */
    public function removeAlias($alias)
    {
        unset($this->aliasDefinitions[$alias]);
    }

    /**
     * @param $alias
     * @return bool
     */
    public function hasAlias($alias)
    {
        return isset($this->aliasDefinitions[$alias]);
    }

    /**
     * @return Alias[]
     */
    public function getAliases()
    {
        return $this->aliasDefinitions;
    }

    /**
     * @param $alias
     * @return Alias
     */
    public function getAlias($alias)
    {
        if (!isset($this->aliasDefinitions[$alias])) {
            throw new InvalidArgumentException(sprintf('Псевдоним сервиса "%s" не найден.', $alias));
        }

        return $this->aliasDefinitions[$alias];
    }

    private function createService(Definition $definition, $id)
    {
        if ($definition->isDeprecated()) {
            @trigger_error($definition->getDeprecationMessage($id), E_USER_DEPRECATED);
        }

        $parameterBag = $this->getParameterBag();

        if (null !== $definition->getFile()) {
            require_once $parameterBag->resolveValue($definition->getFile());
        }

        $arguments = $this->resolveServices($parameterBag->resolveValue($definition->getArguments()));

        if (null !== $factory = $definition->getFactory()) {
            if (is_array($factory)) {
                $factory = [$this->resolveServices($parameterBag->resolveValue($factory[0])), $factory[1]];
            } elseif (!is_string($factory)) {
                throw new RuntimeException(sprintf('Cannot create service "%s" because of invalid factory', $id));
            }

            $service = call_user_func_array($factory, $arguments);

            if (!$definition->isDeprecated() && is_array($factory) && is_string($factory[0])) {
                $r = new \ReflectionClass($factory[0]);

                if (0 < strpos($r->getDocComment(), "\n * @deprecated ")) {
                    @trigger_error(sprintf('The "%s" service relies on the deprecated "%s" factory class. It should either be deprecated or its factory upgraded.', $id, $r->name), E_USER_DEPRECATED);
                }
            }
        } else {
            $r = new \ReflectionClass($class = $parameterBag->resolveValue($definition->getClass()));
            $service = null === $r->getConstructor() ? $r->newInstance() : $r->newInstanceArgs($arguments);
        }

        foreach ($definition->getMethodCalls() as $call) {
            $this->callMethod($service, $call);
        }

        if (null !== $id && $definition->isShared()) {
            $this->services[$id] = $service;
        }

        return $service;
    }


    public static function getServiceConditionals($value)
    {
        $services = [];

        if (is_array($value)) {
            foreach ($value as $v) {
                $services = array_unique(array_merge($services, self::getServiceConditionals($v)));
            }
        }

        return $services;
    }

    private function callMethod($service, $call)
    {
        $services = self::getServiceConditionals($call[1]);

        foreach ($services as $s) {
            if (!$this->has($s)) {
                return;
            }
        }

        call_user_func_array([$service, $call[0]], $this->resolveServices($this->getParameterBag()->resolveValue($call[1])));
    }

    public function resolveServices($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->resolveServices($v);
            }
        } elseif ($value instanceof Reference) {
            $value = $this->get((string) $value);
        } elseif ($value instanceof Definition) {
            $value = $this->createService($value, null);
        }

        return $value;
    }

    public function register($id, $class = null)
    {
        return $this->setDefinition($id, new Definition($class));
    }

    private function __clone()
    {
    }


}