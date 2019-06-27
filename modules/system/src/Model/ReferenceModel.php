<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\system\Model;


use Pyshnov\Core\Cache\FileCache\FileCache;
use Pyshnov\Core\DB\DB;
use Pyshnov\Core\Helpers\Helpers;
use Pyshnov\form\Form;
use Pyshnov\location\CityCase;
use Pyshnov\system\Plugin\Pagination\Pagination;
use Symfony\Component\Routing\Route;

class ReferenceModel extends BaseModel
{
    public function getReference($type)
    {
        $reference = $this->container->getParameter('system.reference');

        $dependence = $reference[$type]['dependence'];

        $count = DB::select($type . '_id', DB_PREFIX . '_' . $type)->orderBy('name', 'ASC')->execute()->rowCount();

        $page = $this->getParam()->has('page') ? (int)$this->getParam()->get('page') : 1;

        $limit = 20;

        $max_page = ceil($count / $limit);

        if ($page > $max_page) {
            $page = 1;
        }
        $start = ($page - 1) * $limit;

        if ($type == 'country') {
            $stmt = DB::select('*', DB_PREFIX . '_' . $type)->orderBy('name', 'ASC')->execute();
        } else {
            $stmt = DB::select('r.*, c.name AS ' . $dependence . '_name ', DB_PREFIX . '_' . $type . ' r')
                ->leftJoin(DB_PREFIX . '_' . $dependence . ' c', 'r.' . $dependence . '_id', '=', 'c.' . $dependence . '_id')
                ->orderBy($dependence . '_id', 'ASC')
                ->orderBy('name', 'ASC')
                ->limit($limit, $start)->execute();
        }

        if (!empty($res['content'] = $stmt->fetchAll())) {
            $res['type'] = $type;
            $res['info'] = $reference[$type];
            $res['info']['dependence'] = $dependence ? $reference[$dependence] : false;
            $res['info']['dependence']['key'] = $dependence;

            $pagination = new Pagination($count, $limit, $page);
            $pagination->setLink($this->request()->getPathInfo())
                ->setQueryParams($this->getParam()->all())
                ->setMaxItem(6);
            $res['pager'] = $pagination;


            return $res;
        }

        return false;
    }

    public function referenceAdd($type)
    {
        $reference = $this->container->getParameter('system.reference');

        $dependence = $reference[$type]['dependence'];
        $dependence_name = '';

        $dep_id = $dependence . '_id';

        if ($dependence) {
            $query = DB::select($dep_id . ', name', DB_PREFIX . '_' . $dependence)
                ->execute()->fetchAll();
            $form = new Form();

            $option = [];

            foreach ($query as $item) {
                $option[$item[$dep_id]] = $item['name'];
            }

            $dependence_name = $reference[$dependence]['name'][0];

            $dependence = $form->addSelect($dep_id, null, false, $option)
                ->setAttribute(['data-width' => '100%', 'data-container' => 'body']);

            if (count($option) > 15) {
                $dependence->addAttribute('data-live-search', 'true');
            }
        }

        $res['type'] = $type;
        $res['info'] = $reference[$type];
        $res['dependence'] = $dependence;
        $res['dependence_name'] = $dependence_name;

        if ('add' == $this->postParam()->get('do')) {
            $params = $this->postParam()->all();

            if ($params['name']) {
                $data['name'] = $params['name'];
                $data[$dep_id] = $params[$dep_id];
                $data['active'] = 1;
                $data['aliases'] = $params['aliases'] ?: Helpers::translit($data['name']);

                if ($type == 'city') {
                    $declension = [
                        0 => $params['name'] ?? '',
                        1 => $params['genitive'] ?? '',
                        2 => $params['dative'] ?? '',
                        3 => $params['accusative'] ?? '',
                        4 => $params['ablative'] ?? '',
                        5 => $params['prepositional'] ?? '',
                    ];

                    $data['declension'] = serialize(new CityCase($declension));

                    $route = [
                        'name' => 'data.location.' . str_replace("-", "_", $data['aliases']),
                        'route' => serialize(new Route('/' . $data['aliases'] . '/', [
                            '_controller' => '\Pyshnov\data\Controller\DataController::location',
                            '_location' => $data['aliases']
                        ]))
                    ];

                    DB::insert($route, DB_PREFIX . '_router')
                        ->execute();

                    $cache = New FileCache();
                    $cache->remove('router');

                }

                $stmt = DB::insert($data, DB_PREFIX . '_' . $type)->execute();

                if ($stmt) {
                    header("Location: /admin/reference/" . $type . "/");
                    exit();
                }
            } else {
                $this->error()->add('Поле "Название" является обязательным');
            }
        }

        return $res;
    }

    /**
     * Вернет данные из базы
     *
     * @param          $type - country|region|city|district|metro
     * @param bool|int $city_id - true вернет только для текущего города
     * @param bool     $active - true вернет только активные
     *
     * @return array
     */
    public function getReferenceType($type, $city_id = false, $active = false)
    {
        $stmt = DB::select('*', DB_PREFIX . '_' . $type);
        if ($city_id)
            $stmt->where('city_id', '=', $city_id);
        if ($active)
            $stmt->where('active', '=', $active);
        $res = $stmt->orderBy('name')->execute()->fetchAll();

        return $res;

    }
}