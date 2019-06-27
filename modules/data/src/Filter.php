<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\data;


use Pyshnov\Core\Helpers\Price;

trait Filter
{

    /**
     * Возвращает параметры для выборки объявлений из базы
     *
     * id
     * active - активные или нет
     * status_data - текущий статус обекта (на модерации, активен, отказано...)
     * moderation - ожидают модерации
     * topic_id - категория
     * street_id - улица
     * city_id - город
     * user_id - пользователь
     * phone - телефон
     * price_min - минмальная цена
     * price_max - максимальная цена
     * time_lease - длительно/посуточно
     * posted_days - за какой период (за последние 1,3,7 дней)
     * has_photo - только с фото
     * district_id - район города
     * metro_id - метро
     * furniture
     * refrigerator
     * washing_machine
     * internet
     *
     * @param $params
     * @return array
     */
    public function prepareFilterParameters($params)
    {
        $columns = [];
        $columns_count = [];
        $value = [];

        /**
         * Если пришел id объекта
         * фильтруем по id
         */
        if (isset($params['id'])) {
            if ($id = $params['id']) {
                if (strpos($id, ',') !== false) {
                    $array_id = explode(',', $id);
                    $str_a = [];
                    foreach ($array_id as $k => $id) {
                        if ($id) {
                            $str_a[] = '?';
                        } else {
                            unset($array_id[$k]);
                        }
                    }
                    if (!empty($array_id)) {
                        $str = implode(',', $str_a);
                        $columns[] = 'd.id IN (' . $str . ')';
                        $columns_count[] = 'id IN (' . $str . ')';
                        $value = array_merge($value, $array_id);
                    }
                } else {
                    $columns[] = 'd.id=?';
                    $columns_count[] = 'id=?';
                    $value[] = (int)$id;
                }
            } else {
                unset($params['id']);
            }
        }

        if (isset($params['active'])) {
            $columns[] = 'd.active=?';
            $columns_count[] = 'active=?';
            $value[] = (int)$params['active'];
        }

        /**
         * Фильтр по статусу
         */
        if (isset($params['status_data']) && $params['status_data'] !== '') {
            if (isset($params['moderation']))
                unset($params['moderation']);
            $status_data = (int)$params['status_data'];
            $columns[] = 'd.status_data=?';
            $columns_count[] = 'status_data=?';
            $value[] = $status_data;
        }

        /**
         * На модерации
         */
        if (isset($params['moderation'])) {
            if ($moderation = (int)$params['moderation']) {
                $columns[] = 'd.status_data=?';
                $columns_count[] = 'status_data=?';
                $value[] = $moderation;
            } else {
                unset($params['moderation']);
            }
        }

        if (isset($params['topic_id'])) {
            if (is_array($params['topic_id'])) {
                $str_a = [];
                foreach ($params['topic_id'] as $k => $id) {
                    if ((int)$id != 0) {
                        $str_a[] = '?';
                    } else {
                        unset($params['topic_id'][$k]);
                    }
                }

                $columns[] = 'd.topic_id IN (' . implode(',', $str_a) . ')';
                $columns_count[] = 'topic_id IN (' . implode(',', $str_a) . ')';
                $value = array_merge($value, $params['topic_id']);
            } elseif (false !== strpos($params['topic_id'], ',')) {
                $array_id = explode(',', $params['topic_id']);
                $str_a = [];
                foreach ($array_id as $k => $id) {
                    if ((int)$id != 0) {
                        $str_a[] = '?';
                    } else {
                        unset($array_id[$k]);
                    }
                }
                $columns[] = 'd.topic_id IN (' . implode(',', $str_a) . ')';
                $columns_count[] = 'topic_id IN (' . implode(',', $str_a) . ')';
                $value = array_merge($value, $array_id);
            } else {
                if ((int)$params['topic_id'] != 0) {
                    $columns[] = 'd.topic_id=?';
                    $columns_count[] = 'topic_id=?';
                    $value[] = (int)$params['topic_id'];
                } else {
                    unset($params['topic_id']);
                }
            }
        }

        /**
         * По id улицы
         */
        if (isset($params['street_id'])) {
            if ($street_id = (int)$params['street_id']) {
                $columns[] = 'd.street_id=?';
                $columns_count[] = 'street_id=?';
                $value[] = $street_id;
            } else {
                unset($params['street_id']);
            }
        }

        /**
         * По id города
         */
        if (isset($params['city_id'])) {
            if ($city_id = (int)$params['city_id']) {
                $columns[] = 'd.city_id=?';
                $columns_count[] = 'city_id=?';
                $value[] = $city_id;
            } else {
                unset($params['city_id']);
            }
        }

        /**
         * По id пользователя
         */
        if (isset($params['user_id'])) {
            if ($user_id = (int)$params['user_id']) {
                $columns[] = 'd.user_id=?';
                $columns_count[] = 'user_id=?';
                $value[] = $user_id;
            } else {
                unset($params['user_id']);
            }
        }

        /**
         * По номеру телефона
         * или по части номера
         */
        if (isset($params['phone'])) {
            if ($params['phone']) {
                $phone = preg_replace('/[^\d]/', '', $params['phone']);
                $phone = substr($phone, -10, 10); // Для избежания конфликта 8 или +7
                $columns[] = "d.phone LIKE '%" . $phone . "%'";
                $columns_count[] = "phone LIKE '%" . $phone . "%'";
            } else {
                unset($params['phone']);
            }
        }

        if (isset($params['price_min'])) {
            $price_min = Price::priceDeFormat($params['price_min']);
            if ($price_min) {
                $columns[] = 'd.price>=?';
                $columns_count[] = 'price>=?';
                $value[] = $price_min;
            } else {
                unset($params['price_min']);
            }
        }

        if (isset($params['price_max'])) {
            $price_min = Price::priceDeFormat($params['price_max']);
            if ($price_min) {
                $columns[] = 'd.price<=?';
                $columns_count[] = 'price<=?';
                $value[] = $price_min;
            } else {
                unset($params['price_max']);
            }
        }

        // Длительно или полусотная аренда
        if (isset($params['time_lease']) && $params['time_lease']) {
            $columns[] = 'd.lease_period=?';
            $columns_count[] = 'lease_period=?';
            $value[] = (int)$params['time_lease'];
        }

        /**
         * За какой период
         */
        if (isset($params['posted_days'])) {
            if ($posted_days = (int)$params['posted_days']) {
                if ($posted_days == 1) {
                    $date_limit = strtotime('now 00:00:00');
                } else {
                    $date_limit = strtotime('-' . ($posted_days - 1) . ' day', strtotime('now 00:00:00'));
                }

                $columns[] = 'd.date_added>=?';
                $columns_count[] = 'date_added>=?';
                $value[] = date('Y-m-d H:i:s', $date_limit);
            } else {
                unset($params['posted_days']);
            }
        }

        /**
         * Только с фото
         */
        if (isset($params['has_photo'])) {
            $has_photo = (int)$params['has_photo'];
            if ($has_photo == 1) {
                $columns[] = 'd.image<>?';
                $columns_count[] = 'image<>?';
                $value[] = '';
            } else {
                unset($params['has_photo']);
            }
        }

        if (isset($params['district_id'])) {

            if (is_array($params['district_id'])) {
                $params['district_id'] = implode(',', $params['district_id']);
            }

            if (strpos($params['district_id'], ',') !== false) {
                $district_id = explode(',', $params['district_id']);
                $imp = [];
                foreach ($district_id as $district) {
                    $imp[] = '?';
                    $value[] = (int)$district;
                }
                $columns[] = 'd.district_id IN (' . implode(',', $imp) . ')';
                $columns_count[] = 'district_id IN (' . implode(',', $imp) . ')';
            } elseif ($params['district_id']) {
                $columns[] = 'd.district_id=?';
                $columns_count[] = 'district_id=?';
                $value[] = (int)$params['district_id'];
            } else {
                unset($params['district_id']);
            }
        }

        if (isset($params['metro_id'])) {

            if (is_array($params['metro_id'])) {
                $params['metro_id'] = implode(',', $params['metro_id']);
            }

            if (strpos($params['metro_id'], ',') !== false) {
                $metro_id = explode(',', $params['metro_id']);
                $imp = [];
                foreach ($metro_id as $metro) {
                    $imp[] = '?';
                    $value[] = (int)$metro;
                }
                $columns[] = 'd.metro_id IN (' . implode(',', $imp) . ')';
                $columns_count[] = 'metro_id IN (' . implode(',', $imp) . ')';
            } elseif ((int)$params['metro_id'] > 0) {
                $columns[] = 'd.metro_id=?';
                $columns_count[] = 'metro_id=?';
                $value[] = (int)$params['metro_id'];
            } else {
                unset($params['metro_id']);
            }
        }

        if (isset($params['furniture'])) {
            $furniture = (int)$params['furniture'];
            if ($furniture == 1) {
                $columns[] = 'd.furniture=?';
                $columns_count[] = 'furniture=?';
                $value[] = 1;
            } else {
                unset($params['furniture']);
            }
        }

        if (isset($params['refrigerator'])) {
            $refrigerator = (int)$params['refrigerator'];
            if ($refrigerator == 1) {
                $columns[] = 'd.refrigerator=?';
                $columns_count[] = 'refrigerator=?';
                $value[] = 1;
            } else {
                unset($params['refrigerator']);
            }
        }

        if (isset($params['washing_machine'])) {
            $washing_machine = (int)$params['washing_machine'];
            if ($washing_machine == 1) {
                $columns[] = 'd.washing_machine=?';
                $columns_count[] = 'washing_machine=?';
                $value[] = 1;
            } else {
                unset($params['washing_machine']);
            }
        }

        if (isset($params['internet'])) {
            $internet = (int)$params['internet'];
            if ($internet == 1) {
                $columns[] = 'd.internet=?';
                $columns_count[] = 'internet=?';
                $value[] = 1;
            } else {
                unset($params['internet']);
            }
        }

        $params_sort = [
            'columns' => implode(' AND ', $columns),
            'columns_count' => implode(' AND ', $columns_count),
            'value' => $value,
        ];

        return $params_sort;

    }

    /**
     * Вернет параметры сортировки объявлений в списке
     * проверяет get либо post параметры, если ничего не передано
     * подставит значение по умолчанию
     *
     * @param $params
     * @return string
     */
    public function prepareSortOrder($params)
    {
        $default_sorts = \Pyshnov::config()->get('filter_sort');;

        if (isset($params['order'])) {

            $params['asc'] = strtoupper($params['asc'] ?? 'DESC');

            if ($params['asc'] == 'ASC') {
                $asc = 'ASC';
            } elseif ($params['asc'] == 'DESC') {
                $asc = 'DESC';
            } else {
                $asc = 'DESC';
            }

            switch ($params['order']) {
                case 'id' : {
                    $order = 'd.id ' . $asc;
                    break;
                }
                case 'type' : {
                    $order = 'type_sh ' . $asc;
                    break;
                }
                case 'square_all' : {
                    $order = 'square_all*1 ' . $asc;
                    break;
                }
                case 'floor' : {
                    $order = 'floor*1 ' . $asc;
                    break;
                }
                case 'district' : {
                    $order = 'district ' . $asc;
                    break;
                }
                case 'city' : {
                    $order = 'city ' . $asc;
                    break;
                }
                case 'date_added' : {
                    $order = 'd.date_added ' . $asc;
                    break;
                }
                case 'price' : {
                    $order = 'price ' . $asc;
                    break;
                }
                case 'popular' : {
                    $order = 'view_count ' . $asc;
                    break;
                }
                default : {
                    $order = $default_sorts;
                }
            }

        } else {
            $order = $default_sorts;
        }

        return $order;
    }

}