<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\data;


class RealtyListing extends Realty
{
    /**
     * Вернет ссылку на объект
     * @return string
     */
    public function getLink()
    {
        return $this->get('category')->get('url')
            . \Pyshnov::config()->get('prefix_realty_name')
            . $this->get('id')
            . (\Pyshnov::config()->get('object_html_prefix') ? '.html' : '/');
    }

    /**
     * @return string
     */
    public function getImage()
    {
        if(($image = $this->get('image')) != '') {
            $image = unserialize($image);
            $count = count($image);
            $alt = $image[0]['alt'] ?: $this->get('category')->get('name_list') . ' в аренду, '
                . $this->getAddress();

            $src = \Pyshnov::DATA_IMG_DIR . '/thumbs/' . $image[0]['name'];

            if (!file_exists(\Pyshnov::root() . '/' . $src)) {
                $src = '/uploads/not-foto.jpg';
            }

            $html = '<img src="' . $src . '" alt="' . $alt . '">
            <div class="count-icon">' . $count . ' <i class="fa-fw fa fa-camera"></i></div>';

        } else {
            $html = '<img src="/uploads/not-foto.jpg" alt="Собственник не предоставил фото">';
        }

        return $html;
    }
}