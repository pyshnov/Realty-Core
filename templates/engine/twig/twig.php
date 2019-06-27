<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

/**
 * @param $theme
 * @param $variables
 * @return mixed
 */
function twig_theme_render($theme, $variables)
{
    $template_file = $theme . '.html.twig'; // Полное имя файла шаблона

    $output = Pyshnov::service('twig')->render($template_file, $variables);

    return $output;
}