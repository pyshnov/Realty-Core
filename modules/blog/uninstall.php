<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function blogUninstall() {

    $config = [
            'blog.title',
            'blog.meta_title',
            'blog.meta_description',
            'blog.meta_keywords',
            'blog.per_page',
            'blog.html_postfix',
            'blog.image_big_height',
            'blog.image_big_width',
            'blog.image_preview_height',
            'blog.image_preview_width'
    ];

    \Pyshnov\Core\DB\DB::delete(DB_PREFIX . '_config')
        ->whereIn('setting', $config)
        ->execute();

    $locales = [
        'config.tab_blog',
        'config.blog_title',
        'config.blog_title_desc',
        'config.blog_meta_title' ,
        'config.blog_meta_title_desc',
        'config.blog_meta_description',
        'config.blog_meta_description_desc',
        'config.blog_meta_keywords',
        'config.blog_meta_keywords_desc',
        'config.blog_per_page',
        'config.blog_per_page_desc',
        'config.blog_html_postfix',
        'config.blog_html_postfix_desc',
        'config.blog_image_big_height',
        'config.blog_image_big_height_desc',
        'config.blog_image_big_width',
        'config.blog_image_big_width_desc',
        'config.blog_image_preview_height',
        'config.blog_image_preview_height_desc',
        'config.blog_image_preview_width',
        'config.blog_image_preview_width_desc'
    ];

    $rows = \Pyshnov\Core\DB\DB::select('lid', DB_PREFIX . '_locales_location')
        ->whereIn('name', $locales)
        ->execute()
        ->fetchAll();

    if (!empty($rows)) {
        $lid = [];

        foreach ($rows as $row) {
            $lid[] = $row['lid'];
        }

        \Pyshnov\Core\DB\DB::delete( DB_PREFIX . '_locales_location')->whereIn('lid', $lid)->execute();
        \Pyshnov\Core\DB\DB::delete( DB_PREFIX . '_locales_target')->whereIn('lid', $lid)->execute();
    }

    \Pyshnov\Core\DB\DB::dropTable(DB_PREFIX . '_blog, ' . DB_PREFIX . '_blog_category')->setExists()->execute();

    \Pyshnov\Core\DB\DB::delete(DB_PREFIX . '_router')
        ->whereLike('name', 'blog.%')->execute();

    return true;
}