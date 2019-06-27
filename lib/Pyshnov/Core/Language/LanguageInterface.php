<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Language;


interface LanguageInterface
{
    public function get($name);

    public function add($name, $value);

    /**
     * @return mixed
     */
    public function getLanguage();

    /**
     * @return array
     */
    public function getLocale();

    /**
     * @param array $locale
     */
    public function setLocale(array $locale);

    /**
     * @param $name
     */
    public function addLocale($name);

    public function addLocalesDb(array $locales);
}