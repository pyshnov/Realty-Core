<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\config\Ajax;


use Pyshnov\Core\Ajax\AjaxResponse;
use Pyshnov\Core\DB\DB;

class ConfigAjax extends AjaxResponse
{
    public function runAction()
    {
        $res = false;

        if ($action = $this->request()->get('action', false)) {
            if (method_exists($this, $action)) {
                if ($this->$action()) {
                    $res = true;
                }
            }
        }

        return $this->render($res);
    }

    public function updateConfig()
    {
        if ($data = $this->request()->request->get('data')) {

            foreach ($data as $key => $value) {

                if ($value === 'true') {
                    $value = 1;
                } elseif ($value === 'false') {
                    $value = 0;
                }

                DB::update(['value' => $value], DB_PREFIX . '_config')
                    ->where('setting', '=', $key)
                    ->execute();

            }

            return true;
        }

        return false;
    }

    public function ajaxSettingAdd()
    {

        if ($data = $this->request()->request->get('data')) {
            parse_str($data, $arr);

            $stmt = DB::select('max(sort) AS sort', DB_PREFIX . '_config')
                ->where('section', '=', $arr['section'])
                ->limit(1)
                ->execute()
                ->fetch();

            $title = 'config.' . strtolower($arr['setting']);

            $params = [
                'setting' => $arr['setting'],
                'value' => $arr['value'] ?: 0,
                'title' => $title,
                'sort' => $stmt['sort'] + 1,
                'type' => $arr['type'],
                'section' => $arr['section']
            ];

            $stmt = DB::insert($params, DB_PREFIX . '_config')->execute(true);

            if ($stmt) {

                $locales = [
                    $title => $arr['name'],
                    $title . '_desc' => $arr['desc'],
                ];

                $this->get('language')->addLocalesDb($locales);

                return true;
            }
        }

        return false;
    }

}