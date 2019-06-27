<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\sitemap\Controller;


use Pyshnov\Core\Controller\BaseController;
use Pyshnov\Core\DB\DB;

class SiteMap extends BaseController
{
    protected $priority;
    protected $host;
    protected $file;


    protected $domain;
    protected $path;
    protected $filename = 'sitemap.xml';
    protected $files;

    public function execute()
    {
        $this->priority = [
            'news' => $this->config()->get('sitemap.priority_news'),
            'topic' => $this->config()->get('sitemap.priority_topic'),
            'page' => $this->config()->get('sitemap.priority_page'),
            'data' => $this->config()->get('sitemap.priority_data')
        ];
        $this->domain = $this->request()->getScheme() . '://' . $this->request()->getHost();
        $this->path = $this->get('kernel')-> getCacheDir();
    }

    public function sitemap()
    {
        $file = $this->path . '/' . $this->filename;

        if (file_exists($file)) {
            if( filemtime($file) + $this->config()->get('sitemap.sitemap_life_time') < time()) {
                $this->createSitemap();
                $this->createSitemapIndex();
            }
        } else {
            $this->createSitemap();
            $this->createSitemapIndex();
        }

        header("Content-Type: text/xml");
        echo file_get_contents($file);

        exit();
    }

    private function createSitemap()
    {
        $category = $this->get('category');

        $category_links = $category->getLinks();

        // Если в настройка включено
        // выводить категории в sitemap
        if($this->config()->get('sitemap.topics_enable')) {

            $cites = DB::select('aliases', DB_PREFIX . '_city')
                ->where('active', '=', 1)
                ->execute()
                ->fetchAll();

            $priority = str_replace(',', '.', $this->priority['topic']);
            $change_freq = $this->config()->get('sitemap.change_freq_topic');

            $urls = [];

            foreach ($cites as $item) {
                foreach ($category_links as $alias) {
                    $urls[] = [
                        'url' => $this->domain . '/' . $item['aliases'] .  $alias,
                        'change_freq' => $change_freq,
                        'priority' => $priority
                    ];
                }
            }

            if(!empty($urls)) {
                $this->startSitemap('sitemap-category.xml' , $urls);
            }
        }

        if($this->config()->get('sitemap.data_enable')) {

            $prefix = $this->config()->get('prefix_realty_name');
            $postfix = $this->config()->get('object_html_prefix') ? '.html' : '/';

            $priority = str_replace(',', '.', $this->priority['data']);
            $change_freq = $this->config()->get('sitemap.change_freq_data');

            $query = DB::select('id, topic_id', DB_PREFIX . '_data')
                ->where('active', '=', 1)
                ->execute()
                ->fetchAll();

            if(!empty($query)) {

                $urls = [];

                foreach($query as $key => $value){

                    $url = $this->domain
                        . $category_links[$value['topic_id']]
                        . $prefix
                        . $value['id']
                        . $postfix;

                    $urls[] = [
                        'url' => $url,
                        'change_freq' => $change_freq,
                        'priority' => $priority
                    ];
                }

                if(!empty($urls)) {
                    $this->startSitemap('sitemap-data.xml' , $urls);
                }
            }

        }

        // Статичные страницы

        $query = DB::select('alias', DB_PREFIX . '_page')->execute()->fetchAll();

        if(!empty($query)) {

            $postfix = $this->config()->get('page_html_prefix') ? '.html' : '/';

            $priority = str_replace(',', '.', $this->priority['page']);
            $change_freq = $this->config()->get('sitemap.change_freq_page');

            $urls = [];

            foreach ($query as $page) {

                $urls[] = [
                    'url' => $this->domain . '/' . trim($page['alias'], '/') . $postfix,
                    'change_freq' => $change_freq,
                    'priority' => $priority
                ];
            }

            if(!empty($urls)) {
                $this->startSitemap('sitemap-page.xml' , $urls);
            }
        }
    }

    private function startSitemap($filename, $url) {
        $nXML = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
';
        $xml = new \XMLWriter();

        $xml->openMemory();
        $xml->setIndent(true);
        $date = date("Y-m-d");
        foreach ($url as $u) {
            $xml->startElement("url");
            $xml->writeElement("loc", $u['url']);
            $xml->writeElement("lastmod", $date);
            $xml->writeElement("changefreq", $u['change_freq']);
            $xml->writeElement("priority", $u['priority']);
            $xml->endElement();
        }
        $nXML .= $xml->outputMemory();
        $nXML .= '</urlset>';

        $f = fopen($this->path . '/' . $filename, 'w');
        fwrite($f, $nXML);
        fclose($f);
        chmod($this->path . '/' . $filename, 0755);

        $this->files[] = $this->domain . '/' . $filename;
    }

    private function createSitemapIndex()
    {
        $filename = $this->path . '/' . $this->filename;

        $nXML = '<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';
        $xml = new \XMLWriter();

        $xml->openMemory();
        $xml->setIndent(true);
        $date = date("Y-m-d");
        foreach ($this->files as $u) {
            $xml->startElement("sitemap");
            $xml->writeElement("loc", $u);
            $xml->writeElement("lastmod", $date);
            $xml->endElement();
        }
        $nXML .= $xml->outputMemory();
        $nXML .= '</sitemapindex>';

        $f = fopen($filename, 'w');
        fwrite($f, $nXML);
        fclose($f);
        chmod($filename, 0755);
    }
}