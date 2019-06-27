<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\DependencyInjection;


use Pyshnov\Core\DependencyInjection\Exception\InvalidArgumentException;
use Pyshnov\Core\DependencyInjection\Exception\OutOfBoundsException;

// https://github.com/symfony/symfony/blob/master/src/Symfony/Component/DependencyInjection/Definition.php

class Definition
{
    private $class;
    private $file;
    private $factory;
    private $shared = true;
    private $deprecated = false;
    private $deprecationTemplate;
    private $calls = [];
    private $instanceof = [];
    private $autoconfigured = false;
    private $changes = [];

    protected $arguments;

    private static $defaultDeprecationTemplate = 'The "%service_id%" service is deprecated. You should stop using it, as it will soon be removed.';

    /**
     * @param string|null $class The service class
     * @param array       $arguments An array of arguments to pass to the service constructor
     */
    public function __construct($class = null, array $arguments = [])
    {
        if (null !== $class) {
            $this->setClass($class);
        }
        $this->arguments = $arguments;
    }

    /**
     * Возвращает все изменения, отслеживаемые для объекта Definition.
     *
     * @return array Массив изменений для этого объекта
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * Устанавливает отслеживаемые изменения для объекта Definition.
     *
     * @param array $changes
     * @return $this
     */
    public function setChanges(array $changes)
    {
        $this->changes = $changes;

        return $this;
    }

    /**
     * Sets a factory.
     *
     * @param string|array $factory Функция PHP или массив, содержащий класс / Ссылка и метод вызова
     *
     * @return $this
     */
    public function setFactory($factory)
    {
        $this->changes['factory'] = true;

        if (is_string($factory) && strpos($factory, '::') !== false) {
            $factory = explode('::', $factory, 2);
        }

        $this->factory = $factory;

        return $this;
    }

    /**
     * Gets the factory.
     *
     * @return string|array The PHP function or an array containing a class/Reference and a method to call
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Sets the service class.
     *
     * @param string $class The service class
     *
     * @return $this
     */
    public function setClass($class)
    {
        $this->changes['class'] = true;

        $this->class = $class;

        return $this;
    }

    /**
     * Gets the service class.
     *
     * @return string|null The service class
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Sets the arguments to pass to the service constructor/factory method.
     *
     * @param array $arguments An array of arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Adds an argument to pass to the service constructor/factory method.
     *
     * @param mixed $argument An argument
     *
     * @return $this
     */
    public function addArgument($argument)
    {
        $this->arguments[] = $argument;

        return $this;
    }

    /**
     * Устанавливает конкретный аргумент.
     *
     * @param int|string $index
     * @param mixed      $argument
     *
     * @return $this
     *
     * @throws OutOfBoundsException When the replaced argument does not exist
     */
    public function replaceArgument($index, $argument)
    {
        if (0 === count($this->arguments)) {
            throw new OutOfBoundsException('Невозможно заменить аргументы, если они еще не установлен.');
        }

        if (is_int($index) && ($index < 0 || $index > count($this->arguments) - 1)) {
            throw new OutOfBoundsException(sprintf('Индекс "%d" не находится в диапазоне [0, %d].', $index, count($this->arguments) - 1));
        }

        if (!array_key_exists($index, $this->arguments)) {
            throw new OutOfBoundsException(sprintf('Аргумент "%s" не существует.', $index));
        }

        $this->arguments[$index] = $argument;

        return $this;
    }

    public function setArgument($key, $value)
    {
        $this->arguments[$key] = $value;

        return $this;
    }

    /**
     * Gets the arguments to pass to the service constructor/factory method.
     *
     * @return array The array of arguments
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Gets an argument to pass to the service constructor/factory method.
     *
     * @param int|string $index
     *
     * @return mixed The argument value
     *
     * @throws OutOfBoundsException When the argument does not exist
     */
    public function getArgument($index)
    {
        if (!array_key_exists($index, $this->arguments)) {
            throw new OutOfBoundsException(sprintf('The argument "%s" doesn\'t exist.', $index));
        }

        return $this->arguments[$index];
    }

    /**
     * Sets the methods to call after service initialization.
     *
     * @param array $calls An array of method calls
     *
     * @return $this
     */
    public function setMethodCalls(array $calls = [])
    {
        $this->calls = [];
        foreach ($calls as $call) {
            $this->addMethodCall($call[0], $call[1]);
        }

        return $this;
    }

    /**
     * Adds a method to call after service initialization.
     *
     * @param string $method The method name to call
     * @param array  $arguments An array of arguments to pass to the method call
     *
     * @return $this
     *
     * @throws InvalidArgumentException on empty $method param
     */
    public function addMethodCall($method, array $arguments = [])
    {
        if (empty($method)) {
            throw new InvalidArgumentException('Method name cannot be empty.');
        }
        $this->calls[] = [$method, $arguments];

        return $this;
    }

    /**
     * Removes a method to call after service initialization.
     *
     * @param string $method The method name to remove
     *
     * @return $this
     */
    public function removeMethodCall($method)
    {
        foreach ($this->calls as $i => $call) {
            if ($call[0] === $method) {
                unset($this->calls[$i]);
                break;
            }
        }

        return $this;
    }

    /**
     * Check if the current definition has a given method to call after service initialization.
     *
     * @param string $method The method name to search for
     *
     * @return bool
     */
    public function hasMethodCall($method)
    {
        foreach ($this->calls as $call) {
            if ($call[0] === $method) {
                return true;
            }
        }

        return false;
    }

    /**
     * Возвращает методы вызова после инициализации службы.
     *
     * @return array An array of method calls
     */
    public function getMethodCalls()
    {
        return $this->calls;
    }

    /**
     * Sets the definition templates to conditionally apply on the current definition, keyed by parent interface/class.
     *
     * @param array $instanceof
     * @return $this
     */
    public function setInstanceofConditionals(array $instanceof)
    {
        $this->instanceof = $instanceof;

        return $this;
    }

    /**
     * Gets the definition templates to conditionally apply on the current definition, keyed by parent interface/class.
     *
     * @return ChildDefinition[]
     */
    public function getInstanceofConditionals()
    {
        return $this->instanceof;
    }

    /**
     * Sets whether or not instanceof conditionals should be prepended with a global set.
     *
     * @param bool $autoconfigured
     *
     * @return $this
     */
    public function setAutoconfigured($autoconfigured)
    {
        $this->changes['autoconfigured'] = true;

        $this->autoconfigured = $autoconfigured;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoconfigured()
    {
        return $this->autoconfigured;
    }

    /**
     * Sets a file to require before creating the service.
     *
     * @param string $file A full pathname to include
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->changes['file'] = true;

        $this->file = $file;

        return $this;
    }

    /**
     * Gets the file to require before creating the service.
     *
     * @return string|null The full pathname to include
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sets if the service must be shared or not.
     *
     * @param bool $shared Whether the service must be shared or not
     *
     * @return $this
     */
    public function setShared($shared)
    {
        $this->changes['shared'] = true;

        $this->shared = (bool)$shared;

        return $this;
    }

    /**
     * Whether this service is shared.
     *
     * @return bool
     */
    public function isShared()
    {
        return $this->shared;
    }

    /**
     * Является ли это определение устаревшим, значит, его больше не следует вызывать.
     *
     * @param bool   $status
     * @param string $template Сообщение шаблона для использования, если определение устарело
     *
     * @return $this
     *
     * @throws InvalidArgumentException When the message template is invalid.
     */
    public function setDeprecated($status = true, $template = null)
    {
        if (null !== $template) {
            if (preg_match('#[\r\n]|\*/#', $template)) {
                throw new InvalidArgumentException('Недопустимые символы, найденные в шаблоне.');
            }

            if (false === strpos($template, '%service_id%')) {
                throw new InvalidArgumentException('The deprecation template must contain the "%service_id%" placeholder.');
            }

            $this->deprecationTemplate = $template;
        }

        $this->changes['deprecated'] = true;

        $this->deprecated = (bool)$status;

        return $this;
    }

    /**
     * Является ли это определение устаревшим, значит, его больше не следует вызывать.
     *
     * @return bool
     */
    public function isDeprecated()
    {
        return $this->deprecated;
    }

    /**
     * Сообщение для использования, если это определение устарело.
     *
     * @param string $id Service id relying on this definition
     *
     * @return string
     */
    public function getDeprecationMessage($id)
    {
        return str_replace('%service_id%', $id, $this->deprecationTemplate ?: self::$defaultDeprecationTemplate);
    }

}