<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Template;

use Pyshnov\Core\Helpers\Helpers;
use Pyshnov\user\User;

class TwigExtension extends \Twig_Extension
{
    public function getFunctions()
    {

        $safe = ['is_safe' => ['html']];
        $env  = ['needs_environment' => true];
        $deprecated = ['deprecated' => true];

        return [
            new \Twig_SimpleFunction('city_name', [$this, 'getCity'])

        ];
    }

    public function getFilters()
    {
        $safe = ['is_safe' => ['html']];
        $env  = ['needs_environment' => true];
        $deprecated = ['deprecated' => true];

        return [
            new \Twig_SimpleFilter('t', 'Pyshnov::t'),
            new \Twig_SimpleFilter('plural', [$this, 'plural'], $safe),
            new \Twig_SimpleFilter('date_format', 'Pyshnov\Core\Helpers\Helpers::dateFormat', $safe),
            new \Twig_SimpleFilter('price', 'Pyshnov\Core\Helpers\Price::priceFormat', $safe),
        ];
    }

    public function plural($number, $one, $two, $five)
    {
        return Helpers::Plural($number, $one, $two, $five);
    }

    public function getCity($case)
    {
        return \Pyshnov::city()->getDeclension()->get($case);
    }
}