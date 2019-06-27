<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\page\Ajax;


use Pyshnov\Core\Ajax\AjaxResponse;
use Pyshnov\Core\Cache\FileCache\FileCache;
use Pyshnov\Core\DB\DB;

class PageAjax extends AjaxResponse
{
    public function remove()
    {
        $res = false;
        $id = $this->request()->query->get('id');

        if ($id) {
            $row = DB::select('alias', DB_PREFIX . '_page')
                ->where('id', '=', $id)->limit(1)->execute()->fetch();

            DB::delete(DB_PREFIX . '_router')
                ->where('name', '=', 'page.' . str_replace("-", "_", $row['alias']))
                ->execute();
            DB::delete(DB_PREFIX . '_page')->where('id', '=', $id)->execute();

            $cache = New FileCache();
            $cache->remove('router');

            $res = true;
        }

        return $this->render($res);
    }
}