<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\user\Ajax;

use Pyshnov\Core\Ajax\AjaxResponse;
use Pyshnov\Core\DB\DB;

class UserAjax extends AjaxResponse
{
    public function signIn()
    {
        $res = false;

        if ($s = $this->getPostParam('s')) {

            parse_str($s, $arr);

            if ($arr['email'] && $arr['pass']) {

                $rememberme = isset($arr['rememberme']);

                if($this->get('user.auth')->authorize($arr['email'], $arr['pass'], $rememberme)) {
                    $res = true;
                } else {
                    if($this->session()->has('blockTimeStart')) {
                        $this->setData('blocked');
                        $this->setMessageError('Привышен лимин попыток авторизации. Авторизация с вашего ip временно заморожена.');
                    } else {
                        $this->setMessageError('Неверный логин или пароль');
                    }
                }
            }
        }

        return $this->render($res);
    }

    public function runAction()
    {
        $res = false;
        if ($action = $this->request()->get('action', false)) {
            if (method_exists($this, $action)) {
                $res = $this->$action();
            }
        }

        return $this->render($res);
    }

    public function updateProfile()
    {
        if($this->get('user')->isAnonymous()) {
            return false;
        }

        $request = $this->request()->request;
        $email = $request->get('email');

        if(!$email) {
            $this->error()->add('email не может быть пустым');
        }

        // Если email изменен, проверяем не используется ли он другим пользователем
        if(($email != $this->get('user')->getEmail())
            && !$this->get('user.action')->checkEmail($email)) {
            $this->error()->add('email уже используется');
        }

        $phone = preg_replace('/[^0-9]/', '', $request->get('phone'));

        $data = [
            'fio' => $request->get('fio'),
            'email' => $email,
            'phone' => $phone
        ];

        if (!$this->error()->has()) {
            DB::update($data, DB_PREFIX . '_user')
                ->where('user_id', '=', $this->get('user')->getId())
                ->execute();

            $this->setMessageSuccess('Данные сохранены успешно.');

            return true;
        }

        $this->setMessageError(implode('<br>', $this->error()->get()));
        return false;
    }

    public function updateProfilePass()
    {
        if($this->get('user')->isAnonymous()) {
            return false;
        }
        $request = $this->request()->request;

        $pass1 = $request->get('pass1');
        $pass2 = $request->get('pass2');
        $pass3 = $request->get('pass3');

        if(!$pass1 || !$pass2 || !$pass3) {
            $this->error()->add('Проверте правильность введенных данных');
        }  elseif ($pass2 !== $pass3) {
            $this->error()->add('Пароли не совпадают');
        } else {
            if (!preg_match('/^([a-zA-Z0-9-_]*)$/', $pass2)) {
                $this->error()->add('Пароль может содержать только латинские буквы, цыфры');
            } else {
                if (strlen($pass2) < $this->config()->get('register_min_pass_length')) {
                    $this->error()->add('Пароль слишком короткий');
                }
            }
        }

        if(!$this->error()->has()) {
            $query = DB::select('password', DB_PREFIX . '_user')
                ->where('user_id', '=', $this->get('user')->getId())
                ->execute()->fetch();

            $pass1 = $this->get('user.auth')->hashPassword($pass1);

            if($pass1 != $query['password']) {
                $this->setMessageError('Неверно указан текущий пароль.');
                return false;
            }

            $pass2 =  $this->get('user.auth')->hashPassword($pass2);

            if ($pass2 == $query['password']) {
                $this->setMessageError('Нельзя использовать старый пароль в качестве нового.');
                return false;
            }

            DB::update(['password' => $pass2], DB_PREFIX . '_user')
                ->where('user_id', '=', $this->get('user')->getId())
                ->execute();
            $this->setMessageSuccess('Пароль успешно изменен.');
            return true;
        }

        $this->setMessageError(implode('<br>', $this->error()->get()));

        return false;
    }
}