<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Config;


use Pyshnov\Core\DB\DB;
use Pyshnov\Core\Template\ThemeHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Parser;

class Config implements ConfigInterface
{
    protected $themeHandler;
    protected $rootDir;

    private $settings;

    public function __construct($root, ThemeHandlerInterface $theme_handler)
    {
        $this->rootDir = $root;
        $this->themeHandler = $theme_handler;
    }

    public function init(Request $request)
    {
        $parse = new Parser();

        $content = $parse->parse(file_get_contents($this->rootDir . '/' . \Pyshnov::CONFIG_DIR . '/db.yml'));

        if (!defined('DB_PREFIX')) {
            define('DB_PREFIX', $content['databases']['prefix']);
        }

        DB::init($content['databases']);

        $stmt = DB::select('setting, value', DB_PREFIX . '_config')->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $this->settings[$row['setting']] = $row['value'];
        }

        $url_path = ltrim($request->getPathInfo(), '/');

        if(substr($url_path, 0, 5) == 'admin') {
            $theme = 'backend';
        } elseif ($this->themeHandler->mobileDetect($request, $this->get('allow_smartphone'))) {
            $theme = 'smartphone';
        } else {
            $theme = $this->get('theme');
            if (!$this->themeHandler->hasTheme($theme)) {
                $theme = 'default';
            }
        }

        $this->set('theme', $theme);
        $this->set('theme_pathname', $this->themeHandler->getThemeDirectory($this->get('theme')));

    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return isset($this->settings[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->settings[$key] = $value;
    }

}