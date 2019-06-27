<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\blacklist\Ajax;


use Pyshnov\Core\Ajax\AjaxResponse;
use Pyshnov\Core\DB\DB;

class BlacklistAjax extends AjaxResponse
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

    protected function addPhone()
    {
        if($phone = $this->getPostParam('phone')) {
            $phone = preg_replace('/[^0-9]/', '', $phone);
            $info = $this->getPostParam('info', '');

            if (strlen($phone) > 9) {
                $phone = '7' . substr($phone, -10, 10);

                DB::insert(['phone' => $phone, 'info' => $info], DB_PREFIX . '_blacklist')->execute();

                return true;
            }
        }

        return false;
    }

    protected function removePhone()
    {
        if($id = $this->getPostParam('id')) {
            DB::delete(DB_PREFIX . '_blacklist')->where('id', '=', $id)->execute();

            return true;
        }

        return false;
    }

    protected function removeData()
    {
        if($phone = $this->getPostParam('phone')) {

            $rows = DB::select('id, image', DB_PREFIX . '_data')
                ->where('phone', '=', $phone)
                ->execute()
                ->fetchAll();

            if (!empty($rows)) {
                $id = [];

                foreach ($rows as $row) {

                    $id[] = $row['id'];

                    if ($row['image']) {
                        $img = unserialize($row['image']);
                        $path = $this->get('kernel')->getRootDir() . \Pyshnov::DATA_IMG_DIR . '/';

                        foreach ($img as $item) {
                            @unlink($path . $item['name']);
                            @unlink($path . 'thumbs/' . $item['name']);
                        }
                    }
                }

                DB::delete(DB_PREFIX . '_data')->whereIn('id', $id)->execute();

                return true;
            }

        }

        return false;
    }
}