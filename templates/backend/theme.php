<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function backend_pre_process_user_signin(&$variables) {
    $variables['return_url'] = Pyshnov::request()->getRequestUri();
}

function backend_pre_process_data_admin_add(&$variables) {

    $variables['category']->setClass('form-control')->addAttribute('required');

    $variables['city'] = Pyshnov::city();

}

function backend_pre_process_data_admin_edit(&$variables) {

    $variables['category']->setClass('form-control')->addAttribute('required');
}

/**
 * Функия препроцессора для всей программы
 *
 * @param $variables
 */
function backend_pre_process(&$variables)
{
    $variables['disk_free_space'] = format_size(disk_free_space("."));
    $variables['disk_total_space'] = format_size(disk_total_space("."));

    foreach ($variables['menu']['admin'] as &$variable) {
        if ($variable['below']) {
            foreach ($variable['below'] as &$item) {
                if (isset($item['attributes']) && $item['attributes'] == ' class="active"') {
                    $variable['a_attributes'] = ' class="has-arrow" aria-expanded="true"';
                    $variable['attributes'] = $item['attributes'];
                } else {
                    $variable['a_attributes'] = ' class="has-arrow" aria-expanded="false"';
                }
            }
        }
    }
}

function format_size($arg)
{
    if ($arg > 0) {
        $j = 0;
        $ext = array(" bytes", " Kb", " Mb", " Gb", " Tb");
        while ($arg >= pow(1024, $j))
            ++$j;

        return round($arg / pow(1024, $j - 1) * 100) / 100 . $ext[$j - 1];
    } else return "0 bytes";
}