<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\user;

use Pyshnov\Core\Config\ConfigInterface;
use Pyshnov\Core\Cookies\Cookies;
use Pyshnov\Core\DB\DB;
use Pyshnov\Core\DependencyInjection\ContainerAwareInterface;
use Pyshnov\Core\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

class UserAuth implements ContainerAwareInterface
{
    /**
     * @var User
     */
    private $user;

    protected $salt;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    use ContainerAwareTrait;

    /**
     * @return Request
     */
    protected function request()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * @return ConfigInterface
     */
    protected function config()
    {
        return $this->container->get('config');
    }

    public function init()
    {
        if ($token = $this->getToken()) {
            if ($user = $this->findUserByToken($token)) {
                $this->user->setUser($user);
            } else {
                $this->cleanup();
            }
        } else {
            $this->user->setAnonymousUser();
        }
    }

    public function authorize($email, $password, $remindme = true)
    {
        if ($this->isTemporalBlocked()) {
            return false;
        }

        // Удалем просроченные сессии
        // TODO Нужно вынести в крон
        DB::delete(DB_PREFIX . '_sessions')
            ->where('expires', '<', date('Y-m-d'))
            ->execute();

        if ($email && strlen($password) >= $this->config()->get('register_min_pass_length')) {
            if ($user = $this->findUser($email, $password)) {
                $this->user->setUser($user);
                $this->saveToken($remindme);

                return true;
            }
        }

        return false;
    }

    public function isTemporalBlocked()
    {
        if ($this->config()->get('lock_autorization')) {
            $session = $this->request()->getSession();

            if ($session->has('blockTimeStart')) {
                $period = time() - $session->get('blockTimeStart');
                if ($period > ($this->config()->get('time_block') * 60)) {
                    $session->remove('blockTimeStart');
                    $session->remove('loginAttempt');
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Поиск пользователя в БД по email-паролю
     *
     * @param $email
     * @param $password
     * @return mixed
     */
    public function findUser($email, $password)
    {
        $session = $this->request()->getSession();

        $stmt = DB::select('u.user_id, 
                            u.login, 
                            u.active, 
                            u.reg_date, 
                            u.fio, 
                            u.email, 
                            u.account, 
                            u.group_id, 
                            u.phone, 
                            u.password, 
                            g.alias, 
                            g.name as group_name', DB_PREFIX . '_user u'
        )
            ->leftJoin(DB_PREFIX . '_group g', 'u.group_id', '=', 'g.id')
            ->multiWhere(['active' => 1, 'email' => $email])
            ->orWhere('login', '=', $email)
            ->execute();

        if ($user = $stmt->fetchObject()) {
            if ($this->hashPassword($password) == $user->password) {
                $session->remove('blockTimeStart');
                $session->remove('loginAttempt');

                return $user;
            } elseif ($this->oldHashPassword($password) == $user->password) {

                //TODO Старая версия, это условие необходимо вырезать в дальнейшем
                $params = [
                    'password' => $this->hashPassword($password)
                ];

                DB::update($params, DB_PREFIX . '_user')
                    ->where('user_id', '=', $user->user_id)
                    ->execute();
                $session->remove('blockTimeStart');
                $session->remove('loginAttempt');

                return $user;
            }
        }

        if ($this->config()->get('lock_autorization')) {
            if (!$session->has('loginAttempt')) {
                $session->set('loginAttempt', 1);
            } else {
                $count = $session->get('loginAttempt') + 1;
                $session->set('loginAttempt', $count);
                if ($count >= $this->config()->get('count_login_attempt')) {
                    $session->set('blockTimeStart', time());
                }
            }
        }

        return false;
    }

    /**
     * Проверяем пароль используя старый алгоритм шифрования
     *
     * @deprecated
     *
     * @param $password
     * @return string
     */
    public function oldHashPassword($password)
    {
        return hash('md5', $password);
    }

    /**
     * Хешировать пароль
     *
     * @param $password
     * @return string
     */
    public function hashPassword($password)
    {
        return hash('sha512',$this->getSalt() . $password);
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return hash('sha256', $this->config()->get('password_salt'));
    }

    /**
     * Поиск пользователя в БД по токену
     *
     * @param $token
     * @return mixed
     */
    public function findUserByToken($token)
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
                            g.alias, 
                            g.name as group_name', DB_PREFIX . '_user u')
            ->leftJoin(DB_PREFIX . '_group g', 'u.group_id', '=', 'g.id')
            ->leftJoin(DB_PREFIX . '_sessions s', 'u.user_id', '=', 's.user_id')
            ->multiWhere(['token' => $token, 'active' => 1], '=')
            ->where('expires', '>=', date('Y-m-d'))
            ->execute();
        $row = $stmt->fetchObject();

        return $row;
    }

    /**
     * Создание и сохранение токена в сессии и формирование cookie
     *
     * @param $remindme
     * @return mixed
     */
    public function saveToken($remindme)
    {
        $expire = $remindme ? strtotime('+1 month') : strtotime('+2 day');
        $token = sha1(uniqid());
        $cookie = $remindme
            ? new Cookies('token', $token, $expire)
            : new Cookies('token');
        $cookie->send();
        $this->request()->getSession()->set('token', $token);

        $params = [
            'token' => $token,
            'expires' => is_numeric($expire) ? date('Y-m-d', $expire) : $expire,
            'user_id' => $this->user->getId(),
            'ip' => $this->request()->getClientIp()
        ];

        DB::insert($params, DB_PREFIX . '_sessions')->execute();

        return $token;
    }

    /**
     * Получение токена из сессии или cookies
     *
     * @return bool
     */
    public function getToken()
    {
        return Cookies::get('token', $this->request()->getSession()->get('token')) ?? false;
    }

    /**
     * Разлониняем
     */
    public function logout()
    {
        if ($token = $this->getToken()) {
            $this->deleteTokenDB($token);
        }
        $this->cleanup();
    }

    /**
     * Удаление токена из БД
     *
     * @param $token
     */
    public function deleteTokenDB($token)
    {
        DB::delete(DB_PREFIX . '_sessions')
            ->where('token', '=', $token)
            ->execute();
    }

    /**
     * Очищаем куки, сессию
     */
    public function cleanup()
    {
        $this->user->setAnonymousUser();
        $cookie = new Cookies('token');
        $cookie->delete();
        $this->request()->getSession()->remove('token');
    }
}