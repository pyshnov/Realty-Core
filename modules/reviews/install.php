<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

use Pyshnov\Core\DB\DB;

function reviewsInstall()
{
    $sort = 0;

    $title = 'reviews_enable';

    $params = [
        'setting' => 'reviews.enable',
        'value' => 1,
        'title' => 'config.' . $title,
        'sort' => $sort + 1,
        'type' => 'checkbox',
        'section' => 'reviews'
    ];

    $stmt = DB::insert($params, DB_PREFIX . '_config')
        ->execute(true);
    if($stmt) {
        $file = \Pyshnov::service('module_handler')->getModule('config')->getPath()
            . '/locale/'.\Pyshnov::getConfig('language') . '/messages.php';

        $fp = fopen ($file, "a");

        $tab = '$lang[\'tab_reviews\'] = \'Отзывы\';';
        fwrite($fp, "\r\n" . $tab);

        $str = '$lang[\'' . $title . '\'] = \'Включить расширение Отзывы\';';
        $str_desc = '$lang[\'' . $title . '_desc\'] = \'\';';
        fwrite($fp, "\r\n" . $str . "\r\n" . $str_desc);
        fclose($fp);
        return true;
    }

    return false;
}

/**
 * Создает таблицу расширения в БД
 */
function reviewsCreateDateBase() {
    \Pyshnov\Core\DB\DB::create([
        '`id`' => "int(11) NOT NULL AUTO_INCREMENT",
        '`approved`' => "TINYINT NOT NULL DEFAULT 0",
        '`review`' => "TEXT NOT NULL COMMENT 'Отзыв'",
        '`date`' => "TIMESTAMP NOT NULL COMMENT 'Дата создания'",
        '`author`' => "VARCHAR(200) NOT NULL COMMENT 'Имя автора'",
        '`author_email`' => "VARCHAR(200) NOT NULL COMMENT 'email автора'",
        'PRIMARY KEY (`id`)' => ''
    ], DB_PREFIX . '_reviews', 'ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1')->execute();
}