<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function user_theme_pre_process_user_signin(&$variables) {

    $variables['return_url'] = Pyshnov::request()->server->get('HTTP_REFERER', '/');

}

function user_theme_pre_process_user_data_edit(&$variables) {
    $variables['category']->setValue($variables['object']['topic_id'])->addAttribute('required');
}
