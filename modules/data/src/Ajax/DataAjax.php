<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\data\Ajax;


use Pyshnov\Core\Ajax\AjaxResponse;
use Pyshnov\Core\Cookies\Cookies;
use Pyshnov\Core\DB\DB;

class DataAjax extends AjaxResponse
{
    public function runAction()
    {
        $res = false;

        if ($action = $this->request()->get('action', false)) {

            if (method_exists($this, $action)) {
                if ($data = $this->$action()) {
                    $this->data = $data;
                    $res = true;
                }
            }
        }

        return $this->render($res);
    }

    protected function loadUserSelect()
    {
        $id = $this->getPostParam('id', 0);

        $html = '';
        $stmt = DB::select('user_id, login, email', DB_PREFIX . '_user')->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $html .= '<option value="' . $row['user_id'] . '" ' . ($id == $row['user_id'] ? 'selected' : '') . '>' . ($row['login'] ?: $row['email']) . '</option>';
        }

        return $html;
    }

    protected function actionObject()
    {
        if ($this->get('user')->isAnonymous()) {
            return false;
        }

        $result = '';

        $id = $this->getPostParam('id');
        $action = $this->getPostParam('do');

        $user_id = $this->get('user')->getId();

        // Если пользователь является администратором
        // или если к данной странице имеются ограничения доступу
        // проверяем права на доспут
        // Необходимо для редактирования из админки
        // Если получим false, тогда проверяем принадлежил ли объявление текущему пользователю
        $admin_mode = $this->get('user')->isAdmin()
            || ($this->get('route_match')->hasRequirement('_access') && $this->get('route_match')->isUserAccess());

        switch ($action) {
            case 'activate':
                $id = explode(',', $id);

                $status = 2;

                if ($admin_mode) {
                    $status = 1;
                }

                $stmt = DB::update(['active' => 1, 'status_data' => $status], DB_PREFIX . '_data');

                if ($admin_mode) {
                    $stmt->whereIn('id', $id);
                } else {
                    $stmt->whereIn('id', $id)
                        ->where('user_id', '=', $user_id);
                }

                $stmt->execute();

                if ($stmt) {
                    $this->setMessageSuccess('Деактивировать');
                    $result = 'Деактивировать';
                }

                break;
            case 'moderation':
                $id = explode(',', $id);

                $stmt = DB::update(['active' => 0, 'status_data' => 2], DB_PREFIX . '_data');

                if ($admin_mode) {
                    $stmt->whereIn('id', $id);
                } else {
                    $stmt->whereIn('id', $id)
                        ->where('user_id', '=', $user_id);
                }

                $stmt->execute();

                if ($stmt) {
                    $this->setMessageSuccess('Деактивировать');
                    $result = 'Деактивировать';
                }

                break;
            case 'deactivate':
                $id = explode(',', $id);

                $stmt = DB::update(['active' => 0, 'status_data' => 3], DB_PREFIX . '_data');

                if ($admin_mode) {
                    $stmt->whereIn('id', $id);
                } else {
                    $stmt->whereIn('id', $id)
                        ->where('user_id', '=', $user_id);
                }

                $stmt->execute();

                if ($stmt) {
                    $this->setMessageSuccess('Активировать');
                    $result = 'Активировать';
                }

                break;
            case 'top':

                $stmt = DB::update(['active' => '1', 'date_added' => date('Y-m-d H:i:s', time())], DB_PREFIX . '_data');

                if ($admin_mode) {
                    $stmt->where('id', '=', (int)$id)->execute();
                } else {
                    $stmt->multiWhere(['id' => (int)$id, 'user_id' => $user_id])->execute();
                }

                if ($stmt) {
                    $result = $id;
                }

                break;
            case 'premium':
                $result = $this->service($id, 'premium', 1, $admin_mode, $user_id);
                break;
            case 'remove_premium':
                $result = $this->service($id, 'premium', 0, $admin_mode, $user_id);
                break;
            case 'bold':
                $result = $this->service($id, 'bold', 1, $admin_mode, $user_id);
                break;
            case 'remove_bold':
                $result = $this->service($id, 'bold', 0, $admin_mode, $user_id);
                break;
            case 'vip':
                $result = $this->service($id, 'vip', 1, $admin_mode, $user_id);
                break;
            case 'remove_vip':
                $result = $this->service($id, 'vip', 0, $admin_mode, $user_id);
                break;
            case 'delete':

                $stmt = DB::select('user_id, image', DB_PREFIX . '_data')
                    ->where('id', '=', $id)
                    ->limit(1)
                    ->execute();

                if ($row = $stmt->fetch()) {

                    if ($admin_mode || $user_id == $row['user_id']) {

                        if ($row['image']) {
                            $img = unserialize($row['image']);
                            $path = $this->get('kernel')->getRootDir() . \Pyshnov::DATA_IMG_DIR . '/';

                            foreach ($img as $item) {
                                @unlink($path . $item['name']);
                                @unlink($path . 'thumbs/' . $item['name']);
                            }
                        }

                        DB::delete(DB_PREFIX . '_data')
                            ->where('id', '=', $id)
                            ->execute();

                        $result = 'Удалено';
                    }
                }

                break;
            case 'delete_selected':

                $id = explode(',', $id);

                $query = DB::select('id, image', DB_PREFIX . '_data');

                if ($admin_mode) {
                    $query->whereIn('id', $id);
                } else {
                    $query->whereIn('id', $id)->where('user_id', '=', $user_id);
                }

                $stmt = $query->execute();

                if ($rows = $stmt->fetchAll()) {
                    $id = [];
                    $path = $this->get('kernel')->getRootDir() . \Pyshnov::DATA_IMG_DIR . '/';
                    foreach ($rows as $row) {
                        $id[] = $row['id'];
                        if ($row['image']) {
                            $img = unserialize($row['image']);
                            foreach ($img as $item) {
                                @unlink($path . $item['name']);
                                @unlink($path . 'thumbs/' . $item['name']);
                            }
                        }
                    }

                    DB::delete(DB_PREFIX . '_data')->whereIn('id', $id)->execute();

                    $result = 'Удалено';
                }

                break;
            case 'copy':

                $id = explode(',', $id);

                if ($admin_mode) {
                    $stmt = DB::select('*', DB_PREFIX . '_data')
                        ->whereIn('id', $id)
                        ->execute();
                } else {
                    $stmt = DB::select('*', DB_PREFIX . '_data')
                        ->whereIn('id', $id)
                        ->where('user_id', '=', $user_id)
                        ->execute();
                }

                if ($rows = $stmt->fetchAll()) {
                    foreach ($rows as $row) {
                        unset($row['id']);
                        DB::insert($row, DB_PREFIX . '_data')->execute(true);
                    }
                    $result = 'Удалено';
                }

                break;
        }

        return $result;
    }

    protected function service($id, $key, $value, $admin_mode, $user_id)
    {
        $stmt = DB::update([$key => $value], DB_PREFIX . '_data');

        if ($admin_mode) {
            $stmt->where('id', '=', (int)$id)->execute();
        } else {
            $stmt->multiWhere(['id' => (int)$id, 'user_id' => $user_id])->execute();
        }

        if ($stmt) {
            return $key;
        }

        return '';
    }

    /**
     * @return string
     */
    protected function reloadSelectForm()
    {
        $city_id = $this->getPostParam('city_id');
        $table = $this->getPostParam('table');
        $value = $this->getPostParam('value');

        $rows = DB::select('*', DB_PREFIX . '_' . $table)
            ->where('city_id', '=', $city_id)
            ->orderBy('name', 'ASC')
            ->execute()
            ->fetchAll();

        $html = '';

        if (!empty($rows)) {

            $html .= '<select name="' . $table . '" id="' . $table . '"' . ((count($rows) > 10) ? ' data-live-search="true"' : '') . ' class="form-control" data-container="body" data-width="100%">';
            $html .= '<option value="0">-- Не выбрано --</option>';
            foreach ($rows as $row) {
                $html .= '<option value="' . $row[$table . '_id'] . '"' . (($row[$table . '_id'] == $value && $value != 0) ? ' selected' : '') . '>' . $row['name'] . '</option>';
            }

            $html .= '</select>';

        }

        return $html;
    }


    protected function loadRegionsHtml()
    {
        $html = '';

        $stmt = DB::select('*', DB_PREFIX . '_region')->where('active', '=', 1)->execute()->fetchAll();

        foreach ($stmt as $item) {
            $html .= '<option value="' . $item['region_id'] . '">' . $item['name'] . '</option>';
        }

        return $html;
    }

    protected function loadCityHtml()
    {
        $id = $this->getPostParam('id');

        $rows = DB::select('*', DB_PREFIX . '_city')->where('region_id', '=', $id)->execute()->fetchAll();

        $html = '';

        foreach ($rows as $item) {
            $html .= '<option value="' . $item['city_id'] . '"' . (count($rows) == 1 ? ' selected ' : '') . '>' . $item['name'] . '</option>';
        }

        return $html;
    }

    /**
     * автокомплит улиц
     */
    protected function streetLoad()
    {
        $city_id = $this->request()->query->get('city_id');

        $rows = DB::select('street_id, name', DB_PREFIX . '_street')
            ->where('city_id', '=', $city_id)
            ->orWhere('city_id', '=', 0)
            ->execute()
            ->fetchAll();

        return !empty($rows) ? $rows : false;
    }

    /**
     * Создаст новую жалобу
     *
     * @return bool
     */
    public function addComplaint()
    {
        $id = (int)$this->request()->request->get('id');
        $text = $this->request()->request->get('text');

        if (is_null($id) && is_null($text)) {
            $this->setMessageError('Не удалось получить данные.');

            return false;
        }

        $params = [
            'user_id' => \Pyshnov::user()->getId(),
            'data_id' => $id,
            'reason' => $text,
            'date' => date('Y-m-d H:i:s', time())
        ];

        $stmt = DB::insert($params, DB_PREFIX . '_complaint')
            ->execute(true);
        if ($stmt) {

            if ($this->session()->has('data_complaint')) {
                $comp_id = $this->session()->get('data_complaint');
                array_push($comp_id, $id);
                $id = $comp_id;
            } else {
                $id = (array)$id;
            }

            $this->session()->set('data_complaint', $id);

            $this->setMessageSuccess('Спасибо, мы всё проверим в ближайшее время.');

            return true;

        }

        $this->setMessageError('Упс :( Что то пошло не так.');

        return false;
    }

    /**
     * Удалит жалобу
     *
     * @return bool
     */
    public function deleteComplaint()
    {
        $id = (int)$this->getPostParam('id');

        $stmt = DB::delete(DB_PREFIX . '_complaint')
            ->where('id', '=', $id)
            ->execute();

        if ($stmt) {
            return true;
        }

        return false;
    }

    public function getRealtyPhone()
    {
        if ($id = $this->request()->request->get('id', false)) {
            $stmt = DB::select('phone', DB_PREFIX . '_data')
                ->where('id', '=', $id)
                ->execute();
            $row = $stmt->fetch();
            // На всякий случай берем только цыфры
            $phone = preg_replace('/[^0-9]/', '', $row['phone']);
            if (strlen($phone) < 10)
                return false;
            // берм последние 10 цифр
            $phone = substr($phone, -10);
            $phone = '+7 (' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6, 2) . '-' . substr($phone, 8, 2);

            if (\Pyshnov::config()->get('enable.counter_open_phone') && \Pyshnov::user()->isAuthenticated()) {
                // ведем посчет открыти номеров
                DB::update(['count_open_phone=count_open_phone + 1'], DB_PREFIX . '_user')
                    ->where('user_id', '=', \Pyshnov::user()->getId())
                    ->execute();
            }

            return $phone;
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function addFavorites()
    {
        $id = (int)$this->request()->request->get('id');

        if ($this->get('user')->isAuthenticated()) {
            if (Cookies::has('udf')) {
                $arr = unserialize(Cookies::get('udf'));
                if (!in_array($id, $arr)) {
                    $arr[] = $id;
                }
            } else {
                $arr[] = $id;
            }
            $arr = serialize($arr);
            $cookies = new Cookies('udf', $arr, '+1 month');
            $cookies->send();

            return [
                'title' => 'Убрать из избранного',
                'html' => '<i class="fa fa-star"></i> Из избранного'
            ];
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function removeFavorites()
    {
        $id = (int)$this->request()->request->get('id');

        if ($this->get('user')->isAuthenticated()) {
            if (Cookies::has('udf')) {
                $arr = unserialize(Cookies::get('udf'));

                if (!empty($arr)) {
                    $arr = array_flip($arr);
                    unset($arr[$id]);
                    if (!empty($arr)) {
                        $arr = array_flip($arr);
                        $arr = serialize($arr);
                        $cookies = new Cookies('udf', $arr, '+1 month');
                        $cookies->send();
                    } else {
                        $cookies = new Cookies('udf');
                        $cookies->delete();
                    }
                }

                return [
                    'title' => 'Добавить в избранное',
                    'html' => '<i class="fa fa-star-o"></i> В избранное'
                ];
            }
        }

        return false;
    }

}