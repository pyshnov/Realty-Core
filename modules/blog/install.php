<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function blogInstall() {

    $config = [
        [
            'setting' => 'blog.title',
            'value' => '',
            'title' => 'config.blog_title',
            'sort' => 1,
            'type' => 'text',
            'section' => 'blog'
        ],
        [
            'setting' => 'blog.meta_title',
            'value' => '',
            'title' => 'config.blog_meta_title',
            'sort' => 2,
            'type' => 'text',
            'section' => 'blog'
        ],
        [
            'setting' => 'blog.meta_description',
            'value' => '',
            'title' => 'config.blog_meta_description',
            'sort' => 3,
            'type' => 'text',
            'section' => 'blog'
        ],
        [
            'setting' => 'blog.meta_keywords',
            'value' => '',
            'title' => 'config.blog_meta_keywords',
            'sort' => 4,
            'type' => 'text',
            'section' => 'blog'
        ],
        [
            'setting' => 'blog.per_page',
            'value' => 10,
            'title' => 'config.blog_per_page',
            'sort' => 5,
            'type' => 'text',
            'section' => 'blog'
        ],
        [
            'setting' => 'blog.html_postfix',
            'value' => 0,
            'title' => 'config.blog_html_postfix',
            'sort' => 6,
            'type' => 'checkbox',
            'section' => 'blog'
        ],
        [
            'setting' => 'blog.image_big_height',
            'value' => 800,
            'title' => 'config.blog_image_big_height',
            'sort' => 7,
            'type' => 'text',
            'section' => 'blog'
        ],
        [
            'setting' => 'blog.image_big_width',
            'value' => 1000,
            'title' => 'config.blog_image_big_width',
            'sort' => 8,
            'type' => 'text',
            'section' => 'blog'
        ],
        [
            'setting' => 'blog.image_preview_height',
            'value' => 190,
            'title' => 'config.blog_image_preview_height',
            'sort' => 9,
            'type' => 'text',
            'section' => 'blog'
        ],
        [
            'setting' => 'blog.image_preview_width',
            'value' => 220,
            'title' => 'config.blog_image_preview_width',
            'sort' => 10,
            'type' => 'text',
            'section' => 'blog'
        ]
    ];

    $locales = [
        'config.tab_blog' => 'Блог',
        'config.blog_title' => 'Заголовок для блога',
        'config.blog_title_desc' => 'Будет использоваться как title',
        'config.blog_meta_title' => 'Мета заголовок для блога',
        'config.blog_meta_title_desc' => '',
        'config.blog_meta_description' => 'Краткое описание для блога',
        'config.blog_meta_description_desc' => '',
        'config.blog_meta_keywords' => 'Ключевые слова для блога',
        'config.blog_meta_keywords_desc' => '',
        'config.blog_per_page' => 'Количество записей на страницу',
        'config.blog_per_page_desc' => '',
        'config.blog_html_postfix' => 'Включить .html постфиксы в конец url',
        'config.blog_html_postfix_desc' => '',
        'config.blog_image_big_height' => 'Высота изображения',
        'config.blog_image_big_height_desc' => '',
        'config.blog_image_big_width' => 'Ширина изображения',
        'config.blog_image_big_width_desc' => '',
        'config.blog_image_preview_height' => 'Высота превью изображения',
        'config.blog_image_preview_height_desc' => '',
        'config.blog_image_preview_width' => 'Ширина превью изображения',
        'config.blog_image_preview_width_desc' => ''
    ];

    $stmt = \Pyshnov\Core\DB\DB::insert($config[0], DB_PREFIX . '_config');
    unset($config[0]);
    foreach($config as $datum) {
        $stmt->addValues($datum);
    }

    $stmt->execute();

    Pyshnov::language()->addLocalesDb($locales);

    faqCreateDateBase();

    return true;
}

/**
 * Создает таблицу расширения в БД
 */
function faqCreateDateBase() {
    \Pyshnov\Core\DB\DB::create([
        '`id`' => "int(11) NOT NULL AUTO_INCREMENT",
        '`active`' => "TINYINT(1) NOT NULL DEFAULT 0",
        '`alias`' => "VARCHAR(255) NOT NULL DEFAULT ''",
        '`name`' => "VARCHAR(255) NOT NULL DEFAULT ''",
        '`meta_title`' => "VARCHAR(255) NOT NULL DEFAULT ''",
        '`meta_description`' => "text NOT NULL DEFAULT ''",
        '`meta_keywords`' => "text NOT NULL DEFAULT ''",
        '`anons`' => "text NOT NULL DEFAULT ''",
        '`body`' => "text NOT NULL DEFAULT ''",
        '`image`' => "VARCHAR(255) NOT NULL DEFAULT ''",
        '`category_id`' => "int(10) NOT NULL",
        '`date`' => "DATETIME NOT NULL",
        '`views`' => "int(11) NOT NULL DEFAULT 0",
        'PRIMARY KEY (`id`)',
        'UNIQUE KEY `alias` (`alias`)'
    ], DB_PREFIX . '_blog', 'ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1')->execute();

    \Pyshnov\Core\DB\DB::create([
        '`id`' => "int(11) NOT NULL AUTO_INCREMENT",
        '`name`' => "VARCHAR(255) NOT NULL DEFAULT ''",
        '`alias`' => "VARCHAR(255) NOT NULL DEFAULT ''",
        '`meta_title`' => "VARCHAR(255) NOT NULL DEFAULT ''",
        '`meta_description`' => "text NOT NULL DEFAULT ''",
        '`meta_keywords`' => "text NOT NULL DEFAULT ''",
        'PRIMARY KEY (`id`)',
        'UNIQUE KEY `alias` (`alias`)'
    ], DB_PREFIX . '_blog_category', 'ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1')->execute();
}
