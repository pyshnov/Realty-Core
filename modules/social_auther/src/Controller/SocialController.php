<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\social_auther\Controller;

use Pyshnov\Core\Controller\BaseController;
use Pyshnov\Core\Cookies\Cookies;
use Pyshnov\social_auther\SocialAuther;
use Pyshnov\user\UserAction;

class SocialController extends BaseController {

    public function authorization()
    {
        if($this->request()->query->has('provider')) {
            $provider = ucfirst($this->request()->query->get('provider'));

            $class = '\\Pyshnov\\social_auther\\Adapter\\' . $provider;

            if (class_exists($class)) {
                $adapter = new $class();

                $auther = new SocialAuther($adapter);

                if (!isset($_GET['code'])) {
                    header('location: ' . $auther->getAuthUrl());
                    exit();

                } else {
                    if ($auther->authenticate()) {
                        $user_info = $auther->prepareUserInfo();

                        $user_action = new UserAction();

                        if(!$test = $user_action->getUserByEmail($user_info['email'])) {
                            $user_action->newUser($user_info);
                        }

                        $this->get('user.auth')->authorize($user_info['email'], $user_info['password'], false);

                        if(Cookies::has('back_url')){
                            $back_url = Cookies::get('back_url');
                            $cookie = new Cookies('back_url');
                            $cookie->delete();
                        }else{
                            $back_url = '/account/profile/';
                        }
                        header('Location: ' . $back_url);
                        exit();
                    }
                }
            }

        }


        return $this->render([], 'main');
    }

}