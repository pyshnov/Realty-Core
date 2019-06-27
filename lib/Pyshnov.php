<?php

use \Pyshnov\Core\DependencyInjection\ContainerInterface;

class Pyshnov
{
    const VERSION = '0.0.18';

    const CONFIG_DIR = 'config';

    const DATA_IMG_DIR = '/uploads/data';

    protected static $container;

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container) {
        static::$container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public static function getContainer() {
        return static::$container;
    }

    /**
     * Вернет сервис из контейнера
     * @param $id
     * @return mixed
     */
    public static function service($id) {
        return static::getContainer()->get($id);
    }

    /**
     * Проверит наличие сервиса в конетйнере
     * @param $id
     * @return bool
     */
    public static function hasService($id) {
        return static::getContainer()->has($id);
    }

    /**
     * @return \Pyshnov\Core\Config\ConfigInterface
     */
    public static function config()
    {
        return static::service('config');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public static function request() {
        return static::getContainer()->get('request_stack')->getCurrentRequest();
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    public static function session()
    {
        return static::request()->getSession();
    }

    /**
     * @return \Pyshnov\Core\Routing\RouteMatch
     */
    public static function routeMatch()
    {
        return static::service('route_match');
    }

    /**
     * @return \Pyshnov\Core\PyshnovKernelInterface
     */
    public static function kernel()
    {
        return static::service('kernel');
    }

    /**
     * @return \Pyshnov\user\User
     */
    public static function user()
    {
        return static::service('user');
    }

    /**
     * @return \Pyshnov\Core\Language\LanguageInterface
     */
    public static function language()
    {
        return static::service('language');
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function t($name)
    {
        return static::language()->get($name);
    }

    /**
     * @return \Pyshnov\location\City
     */
    public static function city()
    {
        return static::service('location')->getCity();
    }

    /**
     * Вернет абсолютный путь до файлов программы
     * @return string
     */
    public static function root() {
        return static::getContainer()->get('kernel')->getRootDir();
    }

}