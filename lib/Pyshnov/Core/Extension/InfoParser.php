<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Extension;


use Symfony\Component\Yaml\Parser;

class InfoParser
{
    protected static $parseInfo = [];

    protected static $yamlParser;

    public function parse($filename)
    {
        if(isset(self::$parseInfo[$filename])) {
            return self::$parseInfo[$filename];
        }

        if (null === self::$yamlParser) {
            self::$yamlParser = new Parser();
        }

        $parse_info = [];

        if (file_exists($filename)) {

            $parse_info = self::$yamlParser->parse(file_get_contents($filename));

            if (isset($parse_info['version']) && $parse_info['version'] === 'VERSION') {
                $parse_info['version'] = \Pyshnov::VERSION;
            }

            self::$parseInfo[$filename] = $parse_info;
        }
        return $parse_info;
    }
}