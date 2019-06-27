<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\DependencyInjection;


use Psr\Log\InvalidArgumentException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class YamlFileLoader
{
    protected $container;

    private $yamlParser;

    private $keywords = [
        'alias' => 'alias',
        'class' => 'class',
        'shared' => 'shared',
        'deprecated' => 'deprecated',
        'factory' => 'factory',
        'file' => 'file',
        'arguments' => 'arguments',
        'properties' => 'properties',
        'configurator' => 'configurator',
        'calls' => 'calls'
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function load($resource)
    {

        $content = $this->loadFile($resource);

        // empty file
        if (null === $content) {
            return;
        }

        // parameters
        if (isset($content['parameters'])) {
            if (!is_array($content['parameters'])) {
                throw new InvalidArgumentException(sprintf('"parameters" в  "%s" должен быть массивом. Проверьте синтаксис YAML.', $resource));
            }

            foreach ($content['parameters'] as $key => $value) {
                $this->container->addParameter($key, $this->resolveServices($value));
            }
        }

        // services
        $this->parseDefinitions($content, $resource);

    }

    /**
     * Loads a YAML file.
     *
     * @param string $file
     *
     * @return array The file content
     *
     * @throws InvalidArgumentException when the given file is not a local file or when it does not exist
     */
    protected function loadFile($file)
    {

        if (!stream_is_local($file)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $file));
        }

        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('The service file "%s" is not valid.', $file));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new Parser();
        }

        try {
            $content = $this->yamlParser->parse(file_get_contents($file), Yaml::PARSE_CONSTANT);
        } catch (ParseException $e) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $file), 0, $e);
        }

        return $content;
    }

    /**
     * @param $value
     * @return array|bool|string
     */
    private function resolveServices($value)
    {
        if (is_array($value)) {
            $value = array_map(array($this, 'resolveServices'), $value);
        } elseif (is_string($value) && 0 === strpos($value, '@=')) {
            throw new InvalidArgumentException(sprintf("'%s' является выражением, но выражения не поддерживаются.", $value));
        } elseif (is_string($value) && 0 === strpos($value, '@')) {
            $value = substr($value, 1);

            if ('=' === substr($value, -1)) {
                $value = substr($value, 0, -1);
            }

            $value = new Reference($value);
        }

        return $value;
    }

    /**
     * Parses definitions.
     *
     * @param array  $content
     * @param string $file
     */
    private function parseDefinitions(array $content, $file)
    {
        if (!isset($content['services'])) {
            return;
        }

        if (!is_array($content['services'])) {
            throw new InvalidArgumentException(sprintf('Ключ "services" может быть только массивом. Проверьте синтаксис YAML файла %s.', $file));
        }

        foreach ($content['services'] as $id => $service) {
            $this->parseDefinition($id, $service, $file);
        }
    }

    /**
     * Parses a definition.
     *
     * @param string       $id
     * @param array|string $service
     * @param string       $file
     *
     * @throws InvalidArgumentException When tags are invalid
     */
    private function parseDefinition($id, $service, $file)
    {
        if (is_string($service) && 0 === strpos($service, '@')) {
            $this->container->addAlias($id, substr($service, 1));

            return;
        }

        if (!is_array($service)) {
            throw new InvalidArgumentException(sprintf('Определение сервиса должно быть массивом или строкой, 
                                начинающейся с @ а не "%s" найденой для сервиса "%s" в %s. 
                                Проверьте синтаксис YAML',
                    gettype($service), $id, $file)
            );
        }

        static::checkDefinition($id, $service, $file);

        if (isset($service['alias'])) {
            $public = !array_key_exists('public', $service) || (bool)$service['public'];
            $this->container->addAlias($id, new Alias($service['alias'], $public));

            foreach ($service as $key => $value) {
                if (!in_array($key, ['alias', 'public'])) {
                    @trigger_error(sprintf('Конфигурационный ключ "%s" не поддерживается сервисом "%s",
                                которая определена как псевдоним в "%s".
                                Разрешенные ключи конфигурации для псевдонимов сервисов - это "alias" и "public". 
                                YamfFileLoader будет генерировать исключение в Symfony 4.0 вместо того, 
                                чтобы молча игнорировать неподдерживаемые атрибуты.',
                        $key, $id, $file), E_USER_DEPRECATED
                    );
                }
            }

            return;
        }

        $definition = new Definition();

        if (isset($service['class'])) {
            $definition->setClass($service['class']);
        }

        // передача в качестве аргументов
        if (isset($service['arguments'])) {
            $definition->setArguments($this->resolveServices($service['arguments']));
        }

        // использовать ли этот сервис
        if (isset($service['shared'])) {
            $definition->setShared($service['shared']);
        }

        // Если устарел и будет удален в дальнейшем
        if (array_key_exists('deprecated', $service)) {
            $definition->setDeprecated(true, $service['deprecated']);
        }

        // запустит функцию в другом объекте
        if (isset($service['factory'])) {
            $definition->setFactory($this->parseCallable($service['factory'], 'factory', $id, $file));
        }

        if (isset($service['file'])) {
            $definition->setFile($service['file']);
        }

        // Запуск функции в текущем классе
        if (isset($service['calls'])) {
            if (!is_array($service['calls'])) {
                throw new InvalidArgumentException(sprintf('Parameter "calls" must be an array for service "%s" in %s. Check your YAML syntax.', $id, $file));
            }

            foreach ($service['calls'] as $call) {
                if (isset($call['method'])) {
                    $method = $call['method'];
                    $args = isset($call['arguments']) ? $this->resolveServices($call['arguments']) : [];
                } else {
                    $method = $call[0];
                    $args = isset($call[1]) ? $this->resolveServices($call[1]) : [];
                }

                $definition->addMethodCall($method, $args);
            }
        }

        $this->container->setDefinition($id, $definition);
    }

    /**
     * Проверяет ключевые слова, используемые для определения службы.
     *
     * @param       $id
     * @param array $definition
     * @param       $file
     */
    private function checkDefinition($id, array $definition, $file)
    {
        foreach ($definition as $key => $value) {
            if (!isset($this->keywords[$key])) {
                throw new InvalidArgumentException(sprintf('The configuration key "%s" is unsupported for service definition "%s" in "%s". Allowed configuration keys are "%s".', $key, $id, $file, implode('", "', static::$keywords)));
            }
        }
    }

    /**
     * Parses a callable.
     *
     * @param string|array $callable A callable
     * @param string       $parameter A parameter (e.g. 'factory' or 'configurator')
     * @param string       $id A service identifier
     * @param string       $file A parsed file
     *
     * @throws InvalidArgumentException When errors are occuried
     *
     * @return string|array A parsed callable
     */
    private function parseCallable($callable, $parameter, $id, $file)
    {
        if (is_string($callable)) {
            if ('' !== $callable && '@' === $callable[0]) {
                throw new InvalidArgumentException(sprintf('The value of the "%s" option for the "%s" service must be the id of the service without the "@" prefix (replace "%s" with "%s").', $parameter, $id, $callable, substr($callable, 1)));
            }

            if (false !== strpos($callable, ':') && false === strpos($callable, '::')) {
                $parts = explode(':', $callable);

                return array($this->resolveServices('@' . $parts[0]), $parts[1]);
            }

            return $callable;
        }

        if (is_array($callable)) {
            if (isset($callable[0]) && isset($callable[1])) {
                return array($this->resolveServices($callable[0]), $callable[1]);
            }

            throw new InvalidArgumentException(sprintf('Parameter "%s" must contain an array with two elements for service "%s" in %s. Check your YAML syntax.', $parameter, $id, $file));
        }

        throw new InvalidArgumentException(sprintf('Parameter "%s" must be a string or an array for service "%s" in %s. Check your YAML syntax.', $parameter, $id, $file));
    }

}