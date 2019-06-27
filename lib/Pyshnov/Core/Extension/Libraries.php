<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Extension;


use Pyshnov\Core\Helpers\Directory;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class Libraries
{
    protected $rootDir;

    protected $yamlParser;

    protected static $libraries = [];

    public function __construct($root)
    {
        $this->rootDir = $root;
    }

    /**
     * @param string $pathname
     */
    public function load(string $pathname)
    {
        $file = $pathname . DIRECTORY_SEPARATOR . 'libraries.yml';

        if (!Directory::isAbsolutePath($file)) {
            $file = $this->rootDir . DIRECTORY_SEPARATOR . $file;
        }

        if (!file_exists($file)) {
            return;
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new Parser();
        }

        try {
            $content = $this->yamlParser->parse(file_get_contents($file));
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(sprintf('Файл "%s" является не валидным YAML.', $file), 0, $e);
        }

        // empty file
        if (null === $content) {
            return;
        }

        // not an array
        if (!is_array($content)) {
            throw new \InvalidArgumentException(sprintf('Файл "%s" должен содежать YAML массив.', $file));
        }

        foreach ($content as $route => $value) {

            if (null === $value) {
                continue;
            }

            foreach (['js', 'css', 'import'] as $item) {
                if (isset($value[$item])) {

                    foreach ($value[$item] as $f => $p) {
                        if (is_string($f) && !Directory::isAbsolutePath($f)) {
                            $f = DIRECTORY_SEPARATOR . $pathname . DIRECTORY_SEPARATOR . $f;
                        }

                        static::$libraries[$route][$item][$f] = $p;
                    }
                }
            }
        }
    }

    public function compile()
    {
        $libraries = [];

        $imports = [];

        foreach ($this->getLibraries() as $route => $library) {

            $lib = [
                'js' => [],
                'css' => []
            ];

            foreach (['js', 'css', 'import'] as $type) {

                if ($type == 'js' && !empty($library[$type])) {
                    $lib[$type] = [
                        'head' => [],
                        'footer' => []
                    ];
                    foreach ($library[$type] as $source => $options) {
                        if (!empty($options)) {
                            if (isset($options['minify'])) {
                                // TODO Сжать
                            }
                            if (isset($options['block'])) {
                                $lib[$type][$options['block']][] = $source;
                                continue;
                            }
                        }

                        $lib[$type]['footer'][] = $source;
                    }
                }

                if ($type == 'css' && !empty($library[$type])) {
                    foreach ($library[$type] as $source => $options) {
                        if (!empty($options)) {
                            if (isset($options['minify'])) {
                                // TODO Сжать
                            }
                        }

                        $lib[$type][] = $source;
                    }
                }

                if ($type == 'import' && !empty($library[$type])) {
                    $imports[$route] = $library[$type];
                }
            }

            $libraries[$route] = $lib;

        }

        if (!empty($imports)) {
            foreach ($imports as $route => $import) {
                if (!empty($import)) {
                    foreach ($import as $r) {
                        if (isset($libraries[$r])) {
                            $libraries[$route] = array_merge_recursive($libraries[$route], $libraries[$r]);
                        }
                    }
                }

            }
        }

        static::$libraries = $libraries;
    }

    public function getLibraries()
    {
        return static::$libraries;
    }

    /**
     * @param $route
     * @return array
     */
    public function getLibrary($route)
    {
        return static::$libraries[$route] ?? [
                'js' => [
                    'head' => [],
                    'footer' => []
                ],
                'css' => []
            ];
    }

    /**
     * @param $route
     * @return bool
     */
    public function hasLibrary($route): bool
    {
        return isset(static::$libraries[$route]);
    }

    /**
     * @param        $file
     * @param string $route
     * @param array  $options
     */
    public static function addJs($file, $route = 'all', array $options = [])
    {
        static::$libraries[$route]['js'][$file] = $options;
    }

    /**
     * @param        $file
     * @param string $route
     * @param array  $options
     */
    public static function addCss($file, $route = 'all', array $options = [])
    {
        static::$libraries[$route]['css'][$file] = $options;
    }
}