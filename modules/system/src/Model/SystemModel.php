<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\system\Model;


use Pyshnov\Core\DB\DB;

class SystemModel extends BaseModel
{
    public function getModules()
    {
        $module_handler = \Pyshnov::service('module_handler');
        $modules = $module_handler->getModules();
        $modules += $module_handler->getNotInstalled();

        $modules_sort = [];

        $rows = DB::select('section', DB_PREFIX . '_config')
            ->groupBy('section')
            ->execute()->fetchAll();

        $section = [];

        foreach ($rows as $row) {
            $section[] = $row['section'];
        }

        foreach ($modules as $name => $value) {

            $value->addInfo('config', in_array($value->info['machine_name'], $section));

            if ($value->getOrigin() == 'core') {
                $modules_sort['core'][] = $modules[$name]->getInfo();
            } else {
                $modules_sort['user'][] = $modules[$name]->getInfo();
            }
        }

        return $modules_sort;
    }
}