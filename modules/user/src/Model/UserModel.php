<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\user\Model;


use Pyshnov\Core\DB\DB;
use Pyshnov\system\Model\BaseModel;

class UserModel extends BaseModel
{
    public function getGroupsAll()
    {
        $res = [
            'rows' => false,
            'pager' => ''
        ];

        $rows = DB::select('*', DB_PREFIX . '_group')->orderBy('id')->execute()->fetchAll();

        if (!empty($rows)) {
            $res['rows'] =  $rows;
        }

        return $res;
    }
}