<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\system;


use Pyshnov\Core\DB\DB;

class Cron
{
    public function run()
    {
        @ignore_user_abort(true);
        set_time_limit(240);


        $time_off = time() - \Pyshnov::config()->get('time_deactiv_data') * 3600;
        $date_off = date('Y-m-d H:i:s', $time_off);

        DB::update(['active' => 0, 'status_data' => 4], DB_PREFIX . '_data')
            ->where('date_added', '<=', $date_off)->execute();

        return true;

    }
}