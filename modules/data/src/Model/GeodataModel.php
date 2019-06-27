<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\data\Model;


use Pyshnov\Core\DB\DB;
use Pyshnov\data\Filter;
use Pyshnov\system\Model\BaseModel;

class GeodataModel extends BaseModel
{
    use Filter;

    public function map()
    {
        $data = [];

        if(!$this->config()->get('geodata_enable')) {
            return json_encode($data);
        }

        $objects = $this->getParamObject();

        if(empty($objects)) {
            return json_encode($data);
        }

        foreach($objects as $key => $value) {
            $data[$key]['city_name'] = $value['city_name'];
            $data[$key]['street'] = $value['street_name'];
            $data[$key]['type_sh'] = ($value['topic_id'] == 6 ? $value['topic_id'] : '') . $value['topic_name'];

            if($value['price']) {
                $data[$key]['price'] = $value['price'];
            } else {
                $data[$key]['price'] = '';
            }

            $title = $value['city_name'] . '<br>' . $value['street_name'] . ($value['number'] ? ' ' . $value['number'] : '') . ' - ' . $data[$key]['price'] . ' руб.';

            $data[$key]['title'] = $title;

            $data[$key]['geo_lat'] = $value['geo_lat']; // широта
            $data[$key]['geo_lng'] = $value['geo_lng'];
            $data[$key]['href'] = $value['href'];
        }

        return json_encode($data);

    }

    public function getParamObject()
    {

        $params = $this->getParam()->all();
        $params['active'] = 1;
        $params['city_id'] = $this->get('city')->getId();

        // Получаем параметры для выборки обявлений из базы
        $params_sort = $this->prepareFilterParameters($params);
        //var_dump($params_sort);

        $stmt = DB::select('d.id, d.price, d.geo_lat, d.geo_lng, d.topic_id, d.room_count, d.number, d.address, c.name AS city_name, s.name AS street_name',
            DB_PREFIX . '_data d')
            ->leftJoin(DB_PREFIX . '_city c', 'd.city_id', '=', 'c.city_id')
            ->leftJoin(DB_PREFIX . '_street s', 'd.street_id', '=', 's.street_id')
            ->where($params_sort['columns'])
            ->setValues($params_sort['value'])
            ->execute();
        $rows = $stmt->fetchAll();

        $res = [];

        $category = \Pyshnov::service('category');

        foreach($rows as $row) {
            $topic = $category->getCategory($row['topic_id']);
            $row['topic_name'] = $topic['name_list'];
            $row['href'] = $topic['url'] . $this->config()->get('prefix_realty_name') . $row['id'] . ($this->config()->get('object_html_prefix') ? '.html' : '');
            $res[] = $row;
        }
        return $res;

    }
}