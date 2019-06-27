<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Template;

use Pyshnov\Core\Extension\Extension;
use Symfony\Component\HttpFoundation\Request;

interface ThemeHandlerInterface
{
    /**
     * @return bool
     */
    public function isDesktop();

    /**
     * @return bool
     */
    public function isSmartphone();

    /**
     * @return bool
     */
    public function isTablet();

    /**
     * @param Request $request
     * @param bool    $allow_smartphone
     * @return bool
     */
    public function mobileDetect(Request $request, $allow_smartphone = false);

    public function getList();

    public function getThemeDirectory($theme = null);

    /**
     * @param $name
     * @return bool|Extension
     */
    public function getTheme($name);

    public function addTheme(Extension $theme);

    /**
     * @param $theme
     * @return bool
     */
    public function hasTheme($theme);
}