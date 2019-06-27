<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function pageInstall() {

    setPageRoute('/admin/page/', 'page.admin', 'adminPage');
    setPageRoute('/admin/page/add/', 'page.admin.add', 'adminPageAdd');
    setPageRoute('/admin/page/edit-{id}/', 'page.admin.edit', 'adminPageEdit');

    $route = new \Symfony\Component\Routing\Route('/ajax/page/remove/', [
        '_controller' => '\Pyshnov\page\Ajax\PageAjax::remove'
    ]);

    \Pyshnov\Core\DB\DB::insert([
        'route' => serialize($route),
        'name' => 'page.ajax.remove'
    ], DB_PREFIX . '_router')
        ->execute();

    pageCreateDateBase();

    return true;
}

function setPageRoute($path, $name, $action)
{
    $route = new \Symfony\Component\Routing\Route($path, [
        '_controller' => '\Pyshnov\page\Controller\PageController::' . $action
    ]);

    $res = [
        'route' => serialize($route),
        'name' => $name
    ];

    \Pyshnov\Core\DB\DB::insert($res, DB_PREFIX . '_router')
        ->execute();
}

/**
 * Создает таблицу расширения в БД
 */
function pageCreateDateBase() {
    \Pyshnov\Core\DB\DB::create([
        '`id`' => "int(11) NOT NULL AUTO_INCREMENT",
        '`alias`' => "VARCHAR(255) NOT NULL DEFAULT ''",
        '`title`' => "VARCHAR(255) NOT NULL DEFAULT ''",
        '`meta_title`' => "VARCHAR(255) NOT NULL DEFAULT ''",
        '`meta_description`' => "text NOT NULL DEFAULT ''",
        '`meta_keywords`' => "text NOT NULL DEFAULT ''",
        '`body`' => "text NOT NULL DEFAULT ''",
        '`theme`' => "VARCHAR(100) NOT NULL DEFAULT ''",
        '`date`' => "DATETIME NOT NULL",
        'PRIMARY KEY (`id`)',
        'UNIQUE KEY (`alias`)'
    ], DB_PREFIX . '_page', 'ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1')->execute();
}
