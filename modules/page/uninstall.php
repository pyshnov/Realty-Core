<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function pageUninstall()
{
    \Pyshnov\Core\DB\DB::delete(DB_PREFIX . '_router')
        ->whereLike('name', 'page.%')->execute();

    \Pyshnov\Core\DB\DB::dropTable(DB_PREFIX . '_page')->setExists()->execute();

    return true;
}