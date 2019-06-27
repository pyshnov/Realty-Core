<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\user;


use Pyshnov\Core\DB\DB;
use Pyshnov\system\Model\BaseModel;

class Restore extends BaseModel
{
    /**
     * @var UserAction
     */
    protected $user;

    public function run()
    {
        $res = [
            'form' => 1,
            'success' => false,
            'message' => ''
        ];

        $this->user = new UserAction();

        if ($this->request()->request->get('restore')
            && $this->prepareRestore($this->request()->request->get('email'))
        ) {

            $res['success'] = true;
            $res['message'] = '<h4 class="message--title">Уведомление отправлено</h4>
                <p>Уважаемый посетитель! В связи с тем, что в целях безопасности все пароли хранятся в зашифрованном виде. На e-mail-адрес, указанный Вами при регистрации, было отправлено уведомление для генерации нового пароля. </p>';

        } elseif ($this->request()->query->get('sec')) {
            $user_info = $this->user->getUserById($this->request()->query->get('id'));
            $hash = $this->request()->query->get('sec');

            // Если присланный хэш совпадает с хэшом из БД для соответствующего id.
            if ($user_info->restore == $hash) {
                $res['form'] = 2;

                // Меняем в БД случайным образом хэш, делая невозможным повторный переход по ссылки.
                $this->sendHashToDB($user_info->email, $this->getHash('0'));
                $this->session()->set('user_id', $this->request()->query->get('id'));
            } else {
                $res['form'] = 0;
                $this->error()->add('Некорректная ссылка. Повторите заново запрос восстановления пароля.');
            }
        } elseif ($this->request()->request->get('change_pass')) {
            $res['form'] = 2;
            // Обновляем пароль на новый
            $password = $this->request()->request->get('password_new');
            $password2 = $this->request()->request->get('password_new_repeat');

            if ($password != $password2) {
                $this->error()->add('Пароли не совпадают');
            } elseif (!preg_match('/^([a-zA-Z0-9-_]*)$/', $password)) {
                $this->error()->add('Пароль может содержать только латинские буквы, цыфры');
            } else {
                $length = $this->config()->get('register_min_pass_length');
                if (strlen($password) < $length) {
                    $this->error()->add('Пароль слишком короткий. Не меньше ' . $length . ' символов');
                } else {
                    $password = $this->get('user.auth')->hashPassword($password);
                    DB::update(['password' => $password], DB_PREFIX . '_user')
                        ->where('user_id', '=', $this->session()->get('user_id'))
                        ->execute();
                    $this->session()->remove('user_id');
                    $res['success'] = true;
                    $res['message'] = 'Вы можете войти в личный кабинет используя новый пароль <a href="#" data-toggle="modal" data-target="#modalAuth">Войти</a>';
                }
            }
        }

        return $res;
    }

    public function prepareRestore($email)
    {
        if ($user_info = $this->user->getUserByEmail($email)) {
            $hash = $this->getHash($email);
            $this->sendHashToDB($email, $hash);

            $domain = $this->request()->getScheme() . '://' . $this->request()->getHost();

            $link = $domain . '/restore/?sec=' . $hash . '&id=' . $user_info->user_id;

            $user_name = $user_info->fio ?: $user_info->email;

            $stmt = DB::select('template', DB_PREFIX . '_email')
                ->where('name', '=', 'restore_pass')
                ->execute();

            if ($row = $stmt->fetchObject()) {
                $row->template = stripslashes($row->template);
                $row->template = str_replace("{%username%}", $user_name, $row->template);
                $row->template = str_replace("{%sitehost%}", $domain, $row->template);
                $row->template = str_replace("{%restorelink%}", $link, $row->template);
                $row->template = str_replace("{%ip%}", $this->request()->getClientIp(), $row->template);

                $mail = $this->get('mail');
                $mail->addAddress($email);
                $mail->addEmbeddedImage(\Pyshnov::root() . '/uploads/logo-mail.png', 'logo', '', 'base64', 'image/png');
                $mail->setBody('Восстановление пароля на ' . $this->config()->get('site_name'), $row->template, true);

                $mail->send();
            }

            return true;

        } else {
            $this->error()->add('К сожалению, такой email не найден.<br>
                            Если вы уверены, что данный email существует, пожалуйста, свяжитесь с нами.');

            return false;
        }
    }

    /**
     * Генерация случайного хэша.
     *
     * @param string $string - строка на основе которой готовится хэш.
     * @return string случайный хэш
     */
    public function getHash($string)
    {
        $hash = htmlspecialchars(md5(rand(0, 100) . $string));

        return $hash;
    }

    /**
     * Метод записывает хэш в таблицк пользователей.
     *
     * @param string $email - электронный адрес пользователя, для которого записываем хэш.
     * @param string $hash - хэш.
     * @return boolean резкльтат выполнения операции.
     */
    public function sendHashToDB($email, $hash)
    {

        $stmt = DB::update(['restore' => $hash], DB_PREFIX . '_user')
            ->where('email', '=', $email)->execute();

        if ($stmt) {
            return true;
        }

        return false;
    }
}