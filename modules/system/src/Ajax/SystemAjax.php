<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\system\Ajax;


use Pyshnov\Core\Ajax\AjaxResponse;
use Pyshnov\Core\Cache\FileCache\FileCache;
use Pyshnov\Core\DB\DB;
use Pyshnov\Core\Helpers\Directory;
use Pyshnov\system\Cron;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Route;

class SystemAjax extends AjaxResponse
{
    public function runAction()
    {
        $res = false;

        if ($action = $this->request()->get('action', false)) {
            if (method_exists($this, $action)) {
                $res = $this->$action();
            }
        }

        return $this->render($res);
    }

    protected function updateReferenceStatus()
    {
        $table = $this->getPostParam('table');
        $id = $this->getPostParam('id');
        $value = $this->getPostParam('value');

        $stmt = DB::update(['active' => $value], DB_PREFIX . '_' . $table)
            ->where($table . '_id', '=', $id)
            ->execute();

        if ($stmt) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function deleteReference()
    {
        $table = $this->getPostParam('table');
        $id = $this->getPostParam('id');

        if ($id && $table) {

            if ($table == 'city') {
                $row = DB::select('aliases', DB_PREFIX . '_city')
                    ->where('city_id', '=', $id)
                    ->execute()->fetch();

                $name = 'data.location.' . str_replace("-", "_", $row['aliases']);

                DB::delete(DB_PREFIX . '_router')
                    ->where('name', '=', $name)
                    ->execute();

                $cache = New FileCache();
                $cache->remove('router');
            }
            $stmt = DB::delete(DB_PREFIX . '_' . $table)
                ->where($table . '_id', '=', $id)
                ->execute();
            if ($stmt) {
                return true;
            }
        }

        return false;
    }

    protected function updateReference()
    {
        $table = $this->getPostParam('table');
        $id = $this->getPostParam('id');
        $field = $this->getPostParam('field');
        $value = $this->getPostParam('value');

        if ($table == 'city' && $field == 'aliases') {
            $row = DB::select('aliases', DB_PREFIX . '_city')
                ->where('city_id', '=', $id)
                ->execute()->fetch();

            $route =  [
                'name' => 'data.location.' . str_replace("-", "_", $value),
                'route' => serialize(new Route('/' . $value . '/', [
                    '_controller' => '\Pyshnov\data\Controller\DataController::location',
                    '_location' => $value
                ]))
            ];

            DB::update($route, DB_PREFIX . '_router')
                ->where('name', '=', 'data.location.' . str_replace("-", "_", $row['aliases']))
                ->execute();

            $cache = New FileCache();
            $cache->remove('router');
        }

        $stmt = DB::update([$field => $value], DB_PREFIX . '_' . $table)
            ->where($table . '_id', '=', $id)
            ->execute();

        if ($stmt) {
            return true;
        }

        return false;
    }

    protected function loadReferenceData()
    {
        $table = $this->getPostParam('table');
        $value = $this->getPostParam('value');

        $stmt = DB::select($table . '_id, name', DB_PREFIX . '_' . $table)->execute();
        $rows = $stmt->fetchAll();
        if ($rows && count($rows) > 1) {

            $html = '<select class="editSelect selectpicker" data-live-search="true">';
            foreach ($rows as $row) {
                if ($row['name'] == trim($value)) {
                    $html .= '<option value="' . $row[$table . '_id'] . '" selected>' . $row['name'] . '</option>';
                } else {
                    $html .= '<option value="' . $row[$table . '_id'] . '">' . $row['name'] . '</option>';
                }
            }
            $html .= '</select>';

            $this->data = $html;

            return true;
        }

        return false;
    }

    /**
     * Установка расширения
     *
     * @return bool
     */
    public function installModule()
    {
        $name = $this->request()->query->get('name');

        $module = $this->get('module_handler')->getNotInstalled()[$name];

        if($module) {

            $params = [
                'name' => $name,
                'active' => 1
            ];

            $file_install = $this->get('kernel')->getRootDir() . '/' . $module->getPathname() . '/install.php';

            if(file_exists($file_install)) {

                require_once $file_install;

                $function = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name)))) . 'Install';

                if(function_exists($function)) {
                    $function();
                }
            }

            DB::insert($params, DB_PREFIX . '_modules')
                ->execute();

            return true;
        }
        return false;
    }

    public function removeModule()
    {
        $name = $this->request()->query->get('name');

        $module = $this->get('module_handler')->getModule($name);

        if($module) {
            $file_install = $this->get('kernel')->getRootDir() . '/' . $module->getPathname() . '/uninstall.php';

            if(file_exists($file_install)) {

                require_once $file_install;

                $function = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name)))) . 'Uninstall';

                if(function_exists($function)) {
                    $function();
                }
            }

            DB::delete(DB_PREFIX . '_modules')
                ->where('name', '=', $name)
                ->execute();

            return true;
        }
        return false;
    }

    /**
     * Вкл./Откл. расширение
     *
     * @return bool
     */
    public function moduleEnable()
    {
        $name = $this->getPostParam('name');
        $val = (int)$this->getPostParam('val');

        DB::update(['active' => $val], DB_PREFIX . '_modules')
            ->where('name', '=', $name)
            ->execute();

        return true;
    }

    public function clearCache()
    {
        $do = $this->request()->query->get('do');

        $cache_dir = $this->get('kernel')->getCacheDir() . '/';

        if(!is_dir($cache_dir)) {
            return false;
        }

        switch ($do) {
            case 'all':
                $cache = New FileCache();
                $cache->dropCache();
                break;
            case 'render':
                $filesystem = new Filesystem();
                $filesystem->remove($cache_dir . 'tmp');
                break;
            case 'router':
                $cache = New FileCache();
                $cache->remove('router');
                break;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function cronRun()
    {
        $cron = new Cron();
        $cron->run();

        return true;
    }
}