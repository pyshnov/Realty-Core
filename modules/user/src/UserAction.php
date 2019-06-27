<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\user;


use Pyshnov\Core\DB\DB;

class UserAction
{
    protected $error;

    /**
     * Вернет информацию о пользоветеле по его id
     *
     * @param $id
     * @return mixed
     */
    public function getUserById($id)
    {
        $stmt = DB::select('u.user_id, 
                            u.login, 
                            u.active, 
                            u.reg_date, 
                            u.fio, 
                            u.email, 
                            u.account, 
                            u.group_id, 
                            u.phone, 
                            u.restore, 
                            g.alias, 
                            g.name as group_name', DB_PREFIX . '_user u')
            ->leftJoin(DB_PREFIX . '_group g', 'u.group_id', '=', 'g.id')
            ->where('user_id', '=', $id)
            ->execute();
        $row = $stmt->fetchObject();

        return $row;
    }

    /**
     * Вернет информацию о пользователе по его email
     *
     * @param $email
     * @return mixed
     */
    public function getUserByEmail($email)
    {
        $stmt = DB::select('u.user_id, 
                            u.login, 
                            u.active, 
                            u.reg_date, 
                            u.fio, 
                            u.email, 
                            u.account, 
                            u.group_id, 
                            u.phone, 
                            u.restore,
                            g.alias, 
                            g.name as group_name', DB_PREFIX . '_user u')
            ->leftJoin(DB_PREFIX . '_group g', 'u.group_id', '=', 'g.id')
            ->where('email', '=', $email)
            ->execute();
        $row = $stmt->fetchObject();

        return $row;
    }

    /**
     * Запишет нового пользователя в базу
     *
     * @param null $params
     * @return bool
     */
    public function newUser($param = null)
    {
        $params = $param ?? $this->prepareRequestParams(\Pyshnov::request()->request->all());

        if (!isset($params['group_id']) || $params['group_id'] == 0) {
            $params['group_id'] = \Pyshnov::config()->get('default_group_id_new_user');
        } else {
            // Если с фронтенда пришол id группы
            // проверим права админа, т.к. только админ может назначать группу
            $user = \Pyshnov::service('user');
            if (!$user->isAdmin()) {
                $params['group_id'] = \Pyshnov::config()->get('default_group_id_new_user');
            }
        }
        if (is_null($param)) {
            if ($this->checkData($params) === false) {
                return false;
            }
            unset($params['password2']);
        }

        if ($params['fio'] == '') {
            $params['fio'] = 'Пользователь';
        }

        $params['reg_date'] = date('Y-m-d H:i:s');
        $params['active'] = 1;

        $params['password'] = \Pyshnov::service('user.auth')->hashPassword($params['password']);

        $stmt = DB::insert($params, DB_PREFIX . '_user')->execute();

        if ($stmt) {
            return true;
        }

        return false;
    }

    /**
     * Подготовит полученные параметры для записи в базу
     * используется для создания нового пользователя и сохранении после редактирования профиля пользователя
     *
     * @param $params
     * @return array
     */
    protected function prepareRequestParams($params)
    {
        $data = [];

        $data['login'] = $params['login'] ?? '';
        $data['fio'] = $params['fio'] ?? '';
        $data['phone'] = $params['phone'] ?? '';
        $data['email'] = $params['email'] ?? '';
        $data['password'] = $params['password'] ?? '';
        $data['password2'] = $params['password2'] ?? '';
        $data['group_id'] = (int)($params['group_id'] ?? 0);

        return $data;
    }

    /**
     * Проверит данные на валидность перед записью в базу
     *
     * @param             $params - Параматры которые требуется проверить
     * @param object|null $user_info - Объект с исходными данными пользователя. Требуется при редактировании данных
     * @return bool
     */
    public function checkData($params, $user_info = null)
    {
        $group_id = $params['group_id'];
        $login = $params['login'];
        $password = $params['password'];
        $email = $params['email'];

        $edit = !is_null($user_info);

        if (!$group_id) {
            $this->setError('Не выбрана группа');
        }

        if ($login) {
            if (!$edit || $login != $user_info->login) {
                if (!preg_match('/^([a-zA-Z0-9-_]*)$/', $login)) {
                    $this->setError('Логин может содержать только латинские буквы, цыфры');
                } else {
                    if ($this->checkLogin($login) === false) {
                        $this->setError('Логин уже используется');
                    }
                }
            }
        }

        if (!$email) {
            $this->setError('Не заполнено поле "Email"');
        } else {
            if (!$edit || $email != $user_info->email) {
                if (!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/",
                    $email)
                ) {
                    $this->setError('Не верный формат Email');
                } else {
                    if ($this->checkEmail($email) === false) {
                        $this->setError('Email уже используется');
                    }
                }
            }
        }

        if ($password) {
            if (!$params['password2']) {
                $this->setError('Не заполнено поле "Повторите пароль"');
            } else {
                if ($password !== $params['password2']) {
                    $this->setError('Пароли не совпадают');
                } else {
                    if (!preg_match('/^([a-zA-Z0-9-_]*)$/', $password)) {
                        $this->setError('Пароль может содержать только латинские буквы, цыфры');
                    } else {
                        if (strlen($password) < \Pyshnov::config()->get('register_min_pass_length')) {
                            $this->setError('Пароль слишком короткий');
                        }
                    }
                }
            }
        } elseif (!$edit) {
            $this->setError('Не заполнено поле "Пароль"');
        }

        if ($this->getError()) {
            return false;
        }

        return true;
    }

    /**
     * Проверит на существование логин в базе
     *
     * @param $login string
     * @return bool
     */
    public function checkLogin($login)
    {
        $stmt = DB::select('login', DB_PREFIX . '_user')
            ->where('login', '=', $login)
            ->execute();

        if ($stmt->rowCount()) {
            return false;
        }

        return true;
    }

    /**
     * Проверит на существование email в базе
     *
     * @param $email string
     * @return bool
     */
    public function checkEmail($email)
    {
        $stmt = DB::select('email', DB_PREFIX . '_user')
            ->where('email', '=', $email)
            ->execute();

        if ($stmt->rowCount()) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getError()
    {
        return $this->error ?? [];
    }

    /**
     * @param string $error
     */
    public function setError(string $error)
    {
        $this->error[] = $error;
    }
}