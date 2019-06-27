<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function location_template_pre_process(&$variables) {

    $variables['city_alias'] = Pyshnov::city()->getAlias();

}