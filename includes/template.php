<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

/**
 * @param $variables
 */
function template_pre_process(&$variables)
{
    $variables += default_variables();

    $template = Pyshnov::service('template');

    $variables['link_base_object'] = getDataBaseLink();
    $variables['title'] = '<h1 class="title">' . $template->getTitle() . '</h1>';
    $variables['meta'] = [
        'title' => $template->getMetaTitle(),
        'description' => $template->getMetaDescription(),
        'keywords' => $template->getMetaKeywords()
    ];
}

function default_variables()
{
    $container = Pyshnov::getContainer();

    $user = $container->get('user');

    $error = $container->get('error_massage');

    $variables = [
        'is_debug' => Pyshnov::kernel()->isDebug(),
        'charset' => Pyshnov::kernel()->getCharset(),
        'user' => $user->getUser(),
        'is_admin' => $user->isAdmin(),
        'is_moderator' => $user->isModerator(),
        'is_authenticated' => $user->isAuthenticated(),
        'is_anonymous' => $user->isAnonymous(),
        'is_main' => \Pyshnov::routeMatch()->getName() == 'main',
        'menu' => $container->get('menu_link')->build(),
        'breadcrumb' => $container->get('breadcrumb'),
        'site_name' => $container->get('config')->get('site_name'),
        'site_version' => Pyshnov::VERSION,
        'key' => Pyshnov::session()->get('key'),
        'user_ip' => Pyshnov::request()->getClientIp(),
        'theme' => '/' . $container->get('config')->get('theme_pathname'),
        'class_content' => strtolower(str_replace('.', '-', \Pyshnov::routeMatch()->getName())),
        'data_img_dir' => Pyshnov::DATA_IMG_DIR,
        'category' => Pyshnov::service('category')->getTopicSelect(),
        'error' => $error->has() ? $error->all() : false
    ];

    return $variables;
}

function theme_render($theme, $variables)
{
    return twig_theme_render($theme, $variables);
}

function getDataBaseLink()
{
    // TODO нужно реализовать формирование ссылки
    $alias = Pyshnov::service('location')->getCity()->getAlias();

    return '/' . $alias . '/arenda/';
}