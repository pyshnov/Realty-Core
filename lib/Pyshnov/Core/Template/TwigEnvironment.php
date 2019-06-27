<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Template;

use Twig_LoaderInterface;

class TwigEnvironment extends \Twig_Environment
{
    function __construct($root, string $cache_dir, Twig_LoaderInterface $loader = null, array $options = [], \Twig_ExtensionInterface $twig_extension)
    {
        if($options['cache'] === TRUE) {
            $options['cache'] = $cache_dir . '/tmp';
        }

        parent::__construct($loader, $options);

        $this->addExtension(new \Twig_Extension_Debug());
        $this->addExtension($twig_extension);

    }
}