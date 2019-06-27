<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

use Pyshnov\form\Form;
use Pyshnov\Core\Cookies\Cookies;

function data_template_pre_process(&$variables)
{
    $variables['favorites'] = Cookies::has('udf') ? unserialize(Cookies::get('udf')) : [];

    if (Pyshnov::request()->attributes->has('_location')) {
        data_filter_pre_process($variables);
    }
}

/**
 * карточка объявления
 *
 * @param $variables
 */
function data_theme_pre_process_data_realty_view(&$variables)
{
    data_filter_pre_process($variables);

    $variables['is_favorite'] = in_array($variables['data']->id, $variables['favorites']);

    $variables['data_complaint'] = new \Pyshnov\data\Block\Complaint();
}

/**
 * редактирование объявления в админке
 *
 * @param $variables
 */
function data_theme_pre_process_data_admin_edit(&$variables)
{
    $variables['category']->setValue($variables['object']['topic_id']);
}

/**
 * добавление нового объявления
 *
 * @param $variables
 */
function data_theme_pre_process_data_add(&$variables)
{
    $variables['category']->addAttribute('required');
}

/**
 * Подготовит параметры для фильтра обектов
 */
function data_filter_pre_process(&$variables)
{
    $form = new Form();
    $reference_model = new \Pyshnov\system\Model\ReferenceModel();
    $city_handler = Pyshnov::service('location')->getCity();

    $variables['district'] = getDistricts($form, $reference_model, $city_handler);
    $variables['metro'] = getMetro($form, $reference_model, $city_handler);

    $query = Pyshnov::request()->query;

    $variables['search_price_min'] = $query->get('price_min') ?? $variables['price_range']['min'];
    $variables['search_price_max'] = $query->get('price_max') ?? $variables['price_range']['max'];

    $variables['category']->setValue(
        $query->get('topic_id', (isset($variables['topic_info']) ? $variables['topic_info']['id'] : 0))
    );
}

/**
 * @param Form                                 $form
 * @param \Pyshnov\system\Model\ReferenceModel $reference_model
 * @param \Pyshnov\location\City               $city_handler
 * @return mixed
 */
function getDistricts($form, $reference_model, $city_handler)
{
    $arr = $reference_model->getReferenceType('district', $city_handler->getId(), true);
    if (!empty($arr)) {
        $option = [];
        foreach ($arr as $item) {
            $option[$item['district_id']] = $item['name'];
        }

        return $form->addSelect('district_id', null, false, $option)->addAttribute('title', Pyshnov::t('system.district_zero_select'));
    }

    return false;
}

/**
 * @param Form                                 $form
 * @param \Pyshnov\system\Model\ReferenceModel $reference_model
 * @param \Pyshnov\location\City               $city_handler
 * @return mixed
 */
function getMetro($form, $reference_model, $city_handler)
{
    $arr = $reference_model->getReferenceType('metro', $city_handler->getId(), true);
    if (!empty($arr)) {
        $option = [];
        foreach ($arr as $item) {
            $option[$item['metro_id']] = $item['name'];
        }

        return $form->addSelect('metro_id', null, false, $option)->addAttribute('title', Pyshnov::t('system.metro_zero_select'));
    }

    return false;
}
