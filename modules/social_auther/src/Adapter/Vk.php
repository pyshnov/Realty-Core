<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\social_auther\Adapter;

class Vk extends Adapter
{

    public function __construct()
    {

        $this->clientId = \Pyshnov::config()->get('social_auther.vk_client_id');
        $this->clientSecret = \Pyshnov::config()->get('social_auther.vk_client_secret');
        $this->redirectUri = \Pyshnov::config()->get('social_auther.vk_redirect_uri');

        $this->socialFieldsMap = array(
            'socialId' => 'uid',
            'email' => 'email',
            'avatar' => 'photo_big',
            'birthday' => 'bdate'
        );
        $this->provider = 'vk';
    }

    /**
     * Get user name or null if it is not set
     *
     * @return string|null
     */
    public function getName()
    {
        $result = null;
        if (isset($this->userInfo['first_name']) && isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['first_name'] . ' ' . $this->userInfo['last_name'];
        } elseif (isset($this->userInfo['first_name']) && !isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['first_name'];
        } elseif (!isset($this->userInfo['first_name']) && isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['last_name'];
        }

        return $result;
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getSocialPage()
    {
        $result = null;
        if (isset($this->userInfo['screen_name'])) {
            $result = 'http://vk.com/' . $this->userInfo['screen_name'];
        }

        return $result;
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        $result = null;
        if (isset($this->userInfo['sex'])) {
            $result = $this->userInfo['sex'] == 1 ? 'female' : 'male';
        }

        return $result;
    }

    /**
     * Authenticate and return bool result of authentication
     *
     * @return bool
     */
    public function authenticate()
    {
        $result = false;
        if (isset($_GET['code'])) {
            $params = array(
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $_GET['code'],
                'redirect_uri' => $this->redirectUri
            );
            $tokenInfo = $this->get('https://oauth.vk.com/access_token', $params);
            if (isset($tokenInfo['access_token'])) {
                $params = array(
                    'uids' => $tokenInfo['user_id'],
                    'fields' => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
                    'access_token' => $tokenInfo['access_token']
                );
                $userInfo = $this->get('https://api.vk.com/method/users.get', $params);
                if (isset($userInfo['response'][0]['uid'])) {
                    $this->userInfo = $userInfo['response'][0];
                    $result = true;
                }
            }
        }

        return $result;
    }

    public function prepareUserInfo()
    {
        if (!is_null($this->getSocialId())) {
            $login = 'vk' . $this->getSocialId();
            $pass = $login . \Pyshnov::config()->get('social_auther.pass_salt');
        } else {
            return false;
        }

        if (!is_null($this->getName())) {
            $fio = $this->getName();
        } else {
            $fio = '';
        }

        if (!is_null($this->getEmail())) {
            $email = $this->getEmail();
        } else {
            $email = $login . '@vk.com';
        }

        $userInfo = [
            'login' => $login,
            'password' => $pass,
            'fio' => $fio,
            'email' => $email
        ];

        return $userInfo;
    }

    /**
     * Prepare params for authentication url
     *
     * @return array
     */
    public function prepareAuthParams()
    {
        return array(
            'auth_url' => 'http://oauth.vk.com/authorize',
            'auth_params' => array(
                'client_id' => $this->clientId,
                'scope' => 'friends,email',
                'redirect_uri' => $this->redirectUri,
                'response_type' => 'code'
            )
        );
    }
}