<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\data;


use Pyshnov\Core\Helpers\Helpers;
use Pyshnov\Core\Helpers\Price;
use Pyshnov\Core\Std;

class Realty extends Std
{
    /**
     * Вернет цену
     *
     * @param string $rub
     * @return string
     */
    public function getPrice($rub = '<i class="fa fa-rub"></i>')
    {
        return Price::priceFormat($this->get('price')) . ' ' . $rub . ' <span>/ ' . ($this->get('lease_period') == 1 ? 'месяц' : 'день') . '</span>';
    }

    /**
     * Вернет площать
     *
     * @param string $default
     * @return string
     */
    public function square($default = '--')
    {
        if ($this->get('topic_id') == 2) {
            return $this->get('square_rooms') ?: $default;
        }

        return $this->get('square_all') ?: $default;
    }

    public function getDataAdd()
    {
        return Helpers::dateFormat($this->get('date_added'));
    }

    public function getFloor($divided = '/')
    {
        return $this->get('floor') ? $this->get('floor') . ($this->get('floor_count') ? $divided . $this->get('floor_count') : '') : '--';
    }

    public function getMetro($prefix = '- ')
    {
        return $this->get('metro') ? $prefix . 'м. ' . $this->get('metro') : '';
    }

    /**
     * Вернет адресс
     *
     * @return string
     */
    public function getAddress()
    {
        $street_name = $this->get('street');

        $number = $this->get('number');

        return $street_name ?
            $street_name . ($number ? ' д.' . $number : '')
            : ($this->has('address') && $this->get('address') ? $this->get('address') : '--');
    }
}