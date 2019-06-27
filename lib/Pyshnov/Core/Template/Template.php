<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Template;


use Pyshnov\Core\DB\DB;
use Pyshnov\Core\Extension\Libraries;
use Pyshnov\Core\DependencyInjection\ContainerAwareInterface;
use Pyshnov\Core\DependencyInjection\ContainerAwareTrait;

class Template extends Layout implements ContainerAwareInterface
{

    public $data;

    /**
     * @var - Абсолютный путь до программы
     */
    protected $rootDir;

    /**
     * @var ThemeHandlerInterface
     */
    protected $themeHandler;

    /**
     * @var - Путь до папки шаблона
     */
    protected $themePath;
    protected $theme;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    public function __construct($root, ThemeHandlerInterface $theme_handler, \Twig_Environment $twig)
    {
        $this->rootDir = $root;
        $this->themeHandler = $theme_handler;
        $this->twig = $twig;

        $this->data = [];
    }

    use ContainerAwareTrait;

    /**
     * @return string
     */
    public function getRootDir():string
    {
        return $this->rootDir;
    }

    /**
     * @return string
     */
    public function getThemePath():string
    {
        if (null === $this->themePath) {
            $this->themePath = $this->getRootDir() . '/' . \Pyshnov::config()->get('theme_pathname');
        }

        return $this->themePath;
    }


    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->addData($key, $value);
        }
    }

    public function addData($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function render($view = null)
    {
        $extension = '.html.twig';

        $view = $view ?? strtolower(str_replace('.', '_', \Pyshnov::routeMatch()->getName()));

        $template_file = $view . $extension;

        $this->data['is_smartphone'] = $this->themeHandler->isSmartphone();
        $this->data['is_tablet'] = $this->themeHandler->isTablet();
        $this->data['is_desktop'] = $this->themeHandler->isDesktop();

        template_pre_process($this->data);
        $preprocessors = [
            'theme_pre_process_' . $view
        ];

        if (is_file($this->getThemePath() . '/theme.php')) {
            require_once $this->getThemePath() . '/theme.php';
        }

        $libraries = new Libraries($this->getRootDir());

        // Ставим системный модуль на первое место
        // что бы сначала отработали системные препроцессоры
        // и только потом остальные модули
        $modules_file = $this->container->get('module_handler')->getPathName();
        asort($modules_file);
        $mc = ['system' => $modules_file['system']];
        unset($modules_file['system']);
        $modules_file = $mc + $modules_file;

        foreach ($modules_file as $module => $path_name) {
            $preprocessors[] = $module . '_template_pre_process';
            $preprocessors[] = $module . '_theme_pre_process_' . $view;
            $libraries->load($path_name);
        }

        $libraries->load(\Pyshnov::config()->get('theme_pathname'));

        $preprocessors[] = \Pyshnov::config()->get('theme') . '_pre_process';
        $preprocessors[] = \Pyshnov::config()->get('theme') . '_pre_process_' . $view;
        foreach ($preprocessors as $preprocessor) {
            if(function_exists($preprocessor)) {
                $preprocessor($this->data);
            }
        }

        // рендерим файл шаблона
        $content = $this->twig->render($template_file, $this->data);

        $libraries->compile();

        // тоько после сборки файла шаблона отбираем необходимые библиотеки
        // что бы успеть подцепить библиотеки из модулей
        $this->data['css'] = $this->prepareStyles($libraries);
        $this->data['js']['head'] = $this->prepareScripts($libraries, 'head');
        $this->data['js']['footer'] = $this->prepareScripts($libraries);
        $this->data['head'] = $this->head();

        if($this->getThemeType() == 'global') {
            return $content;
        }

        return $this->renderHtml($content);
    }

    /**
     * @param           $content
     * @return mixed|string
     */
    protected function renderHtml($content)
    {
        $this->data['content'] = $content;

        $output = $this->twig->render('html.html.twig', $this->data);

        if ($this->container->getParameter('kernel.debug')) {
            $output = str_replace('</body>', $this->debugInfo() . '</body>', $output);
        }

        return $output;

    }

    public function prepareStyles(Libraries $libraries)
    {
        $html = '
    <link href="/core/templates/engine/css/core.css" rel="stylesheet" type="text/css">';

        $lib = $libraries->getLibrary('all');

        foreach ($lib['css'] as $css) {
            $html .= '
    <link href ="' . $css . '" rel="stylesheet" type="text/css">';
        }

        $lib = $libraries->getLibrary(\Pyshnov::routeMatch()->getName());

        foreach ($lib['css'] as $css) {
            $html .= '
    <link href ="' . $css . '" rel="stylesheet" type="text/css">';
        }

        return $html;
    }

    public function prepareScripts(Libraries $libraries, $block = 'footer')
    {
        $html = '';

        if($block == 'head') {
            $html = '
    <script type="text/javascript" src="/core/assets/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="/core/templates/engine/js/engine.js"></script>';
        }

        $lib = $libraries->getLibrary('all');

        foreach ($lib['js'][$block] as $js) {
            $html .= '
    <script type="text/javascript" src="' . $js . '"></script>';
        }

        $lib = $libraries->getLibrary(\Pyshnov::routeMatch()->getName());

        foreach ($lib['js'][$block] as $js) {
            $html .= '
    <script type="text/javascript" src="' . $js . '"></script>';
        }

        return $html;
    }

    /**
     * @return string
     */
    public function head()
    {
        $css = $this->data['css'];
        $js = $this->data['js']['head'];

        $title = $this->replaceHead($this->data['meta']['title']);
        $description = $this->replaceHead($this->data['meta']['description']);
        $keywords = $this->data['meta']['keywords'];

        $meta = '<meta charset="' . \Pyshnov::kernel()->getCharset() . '">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">';
        if (\Pyshnov::config()->get('theme') == 'smartphone') {
            $meta .= '
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="viewport" content="height=device-height, width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">';
        } else {
            $meta .= '
    <meta name="viewport" content="width=device-width, initial-scale=1">';
        }

        $meta .= '
    <title>'.$title.'</title>';
        $meta .= $keywords ? '
    <meta name="keywords" content="'.$keywords.'">' : '';
        $meta .= $description ? '
    <meta name="description" content="'.$description.'">' : '';
        $meta .= '
    <meta name="author" content="Aleksandr Pyshnov">';
        $head = $meta;
        $head .= $css;
        $head .= $js;

        return $head;
    }

    public function replaceHead($str)
    {
        if(!$str) {
            return '';
        }

        $city = \Pyshnov::city();

        $replacement = [
            'city_name' => $city->getName(),
            'site_name' => \Pyshnov::config()->get('site_name')
        ];

        preg_match_all('/\{\{[^\}]+\}\}/', $str, $matches);

        $replace = [];

        foreach ($matches[0] as $match) {
            $str_replace = trim(str_replace(['{{', '}}'], '', $match));
            if(isset($replacement[$str_replace])) {
                $replace[] = $replacement[$str_replace];
            } else {
                if(strpos($str_replace, '|') !== false) {
                    $str_replace = explode('|', $str_replace);
                    $replace[] = $city->getDeclension()->get($str_replace[1]);
                } else {
                    $replace[] = '';
                }
            }
        }

        $matches = str_replace($matches[0], $replace, $str);

        return $matches ?: '';
    }

    private function debugInfo()
    {
        $html = "\n<!--\r\n";
        $exec_time = microtime(true) - \Pyshnov::kernel()->getStartTime();
        $html .= "Запросов к базе данных: ".DB::$countCalls."\r\n";
        $html .= "Страница сгенерирована за: ".$exec_time." секунд\r\n";
        if (function_exists("memory_get_peak_usage")) {
            $html .= "Затрачено оперативной памяти ".round(memory_get_peak_usage() / (1024), 2)." kb \r\n";
        }
        $html .= "-->\n";

        return $html;
    }

}