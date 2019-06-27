<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\data;


use Pyshnov\Core\Helpers\Price;

class RealtyView extends Realty
{
    public function title()
    {

        if ($template = \Pyshnov::config()->get('seo.template_title')) {

            preg_match_all('/\{\{[^\}]+\}\}/', $template, $matches);

            if (!empty($matches)) {
                foreach ($matches[0] as $item) {
                    $res = trim(str_replace(['{{', '}}'], '', $item));
                    if (strpos($res, '.') !== false) {
                        $r = explode('.', $res);
                        $res = $this->get($r[0])->get($r[1]);
                    } else {
                        $res = $this->get($res);
                    }
                    $template = str_replace($item, $res, $template);
                }
            }

            return $template;
        }

        return ($this->topic_id == 6 ? $this->room_count : '')
            . $this->topic->name_list
            . ' в аренду г.' . $this->city_name
            . ' — ' . $this->getPrice();

    }

    public function meta()
    {

        $res = new \stdClass();

        if ($this->get('meta_title')) {
            $res->title = $this->get('meta_title');
        } else {
            $res->title = 'Сдается '
                . $this->topic->meta_title . ': '
                . $this->city_name . ', '
                . $this->getAddress()
                . ($this->getMetro() ? ' (' . $this->getMetro('') . ')' : '') . ', '
                . Price::priceFormat($this->get('price')) . ' руб./'
                . ($this->get('lease_period') == 1 ? 'месяц' : 'день') . ' - без посредников и комиссий на сайте '
                . \Pyshnov::config()->get('site_name');
        }


        $res->description = $this->generateDescription();
        //TODO реализовать генерацию ключевых слов
        $res->keywords = '';

        return $res;

    }

    public function generateDescription()
    {
        if ($this->get('meta_description')) {
            $description = $this->get('meta_description');
        } else {
            $fastquotes = ["\x22", "\x60", '"', "\\", "/", "{", "}", "[", "]", "\t"];
            $fastquotes2 = ["\t", "\n", "\r", '\r', '\n'];

            $story = $this->get('text');
            $story = str_replace("&nbsp;", ' ', $story);
            $story = str_replace('<br />', ' ', $story);
            $story = strip_tags($story);
            $story = trim(str_replace(" ,", ',', stripslashes($story)));
            $story = str_replace($fastquotes, '', $story);
            $story = str_replace($fastquotes2, ' ', $story);

            $description = mb_substr($story, 0, 160, 'utf-8');
        }

        return $description;
    }

    public function getTimeMetro()
    {
        $res = '';
        if ($this->get('time_metro')) {
            $res = $this->get('time_metro') . ' мин. '
                . ($this->get('how_to_get') == 0 ? 'пешком' : 'транспортом');
        }

        return $res;
    }

    public function dataPrint()
    {
        return $this->toArray();
    }

    /**
     * Санузел
     *
     * @return string
     */
    public function getBathroom()
    {
        $res = '';

        if ($bathroom = $this->get('bathroom')) {
            if ($bathroom == 1) {
                $res = 'совмещенный';
            } elseif ($bathroom == 2) {
                $res = 'раздельный';
            } else {
                $res = '2 и более';
            }
        }

        return $res;
    }

}