<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Language;


use Pyshnov\Core\Cache\FileCache\FileCache;
use Pyshnov\Core\DB\DB;
use Symfony\Component\HttpFoundation\RequestStack;

class Language implements LanguageInterface
{
    protected $language;

    protected $locale;

    protected $defaultLocale = 'ru';

    public function __construct($root, $system_locales, RequestStack $request_stack)
    {
        $this->setLocale($system_locales);

        $cache = new FileCache();

        if (!$language = $cache->get('language')) {

            $lang = [];

            $sys_lang = \Pyshnov::config()->get('language');

            $request_stack->getCurrentRequest()->setLocale($sys_lang);
            $request_stack->getCurrentRequest()->setDefaultLocale($this->defaultLocale);

            $stmt = DB::select('l.name, t.translation', DB_PREFIX . '_locales_location l')
                ->leftJoin(DB_PREFIX . '_locales_target t', 'l.lid', '=', 't.lid')
                ->where('langcode', '=', $sys_lang)
                ->execute();

            if($rows = $stmt->fetchAll()) {
                foreach($rows as $row) {
                    $this->add($row['name'], $row['translation']);
                }
            }

            $query = DB::select('filename, filepath', DB_PREFIX . '_locales_file')
                ->where('langcode', '=', $sys_lang)
                ->execute()
                ->fetchAll();

            foreach($query as $data) {

                $file_path = $root . '/' . $data['filepath'];

                if(file_exists($file_path)) {
                    include $file_path;
                    foreach($lang as $key => $value) {
                        $this->add($data['filename'] . '.' . $key, $value);
                    }
                }
            }

            if (\Pyshnov::kernel()->getEnvironment() != 'dev') {
                $cache->save('language', $this->getLanguage());
            }
        } else {
            $this->language = $language;
        }
    }

    public function get($name)
    {
        return $this->language[$name] ?? '';
    }

    public function add($name, $value)
    {
        $this->language[$name] = $value;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return array
     */
    public function getLocale(): array
    {
        return $this->locale;
    }

    /**
     * @param array $locale
     */
    public function setLocale(array $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param $name
     */
    public function addLocale($name)
    {
        $this->locale[] = $name;
    }

    /**
     * Запишет в базу
     *
     * $locales = [
     *      'tab_system' => [
     *          'ru' => 'Общие',
     *          'en' => 'Common'
     *      ],
     *      'tab_smartphone' => 'Смартфоны',
     *      'tab_security' => 'Безопасность',
     *  ];
     *
     * @param array $locales
     */
    public function addLocalesDb(array $locales)
    {
        $query = DB::select('MAX(lid) AS lid', DB_PREFIX . '_locales_target')->execute()->fetch();

        $lid = $query['lid'];

        foreach($locales as $key => $value) {
            $lid++;
            foreach($this->getLocale() as $locale) {
                $data = [
                    'translation' => is_array($value) ? $value[$locale] ?? $value['ru'] : $value,
                    'langcode' => $locale,
                    'lid' => $lid
                ];
                DB::insert($data, DB_PREFIX . '_locales_target')->execute();
            }

            DB::insert(['lid' => $lid, 'name' => $key], DB_PREFIX . '_locales_location')->execute();

        }
    }
}