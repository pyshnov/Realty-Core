<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

/**
 * Created by PhpStorm.
 * User: aleksandr
 * Date: 07.02.17
 * Time: 3:46
 */

namespace Pyshnov\social_auther\Adapter;


class Ok extends Adapter
{
    /**
     * Social Public Key
     *
     * @var string|null
     */
    protected $publicKey = null;

    /**
     * Ok constructor.
     */
    public function __construct()
    {

        $this->clientId = \Pyshnov::config()->get('social_auther.ok_client_id');
        $this->clientSecret = \Pyshnov::config()->get('social_auther.ok_client_secret');
        $this->redirectUri = \Pyshnov::config()->get('social_auther.ok_redirect_uri');
        $this->publicKey = \Pyshnov::config()->get('social_auther.ok_public_key');


        $this->socialFieldsMap = array(
            'socialId' => 'uid',
            'email' => 'email',
            'name' => 'name',
            'avatar' => 'pic_2',
            'sex' => 'gender',
            'birthday' => 'birthday'
        );
        $this->provider = 'ok';
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getSocialPage()
    {
        $result = null;
        if (isset($this->userInfo['uid'])) {
            return 'http://www.odnoklassniki.ru/profile/' . $this->userInfo['uid'];
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
                'code' => $_GET['code'],
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            );

            $tokenInfo = $this->post('http://api.odnoklassniki.ru/oauth/token.do', $params);
            if (isset($tokenInfo['access_token']) && isset($this->publicKey)) {
                $sign = md5("application_key={$this->publicKey}format=jsonmethod=users.getCurrentUser" . md5("{$tokenInfo['access_token']}{$this->clientSecret}"));
                $params = array(
                    'method' => 'users.getCurrentUser',
                    'access_token' => $tokenInfo['access_token'],
                    'application_key' => $this->publicKey,
                    'format' => 'json',
                    'sig' => $sign
                );
                $userInfo = $this->get('http://api.odnoklassniki.ru/fb.do', $params);
                if (isset($userInfo['uid'])) {
                    $this->userInfo = $userInfo;
                    $result = true;
                }
            }
        }

        return $result;
    }

    public function prepareUserInfo()
    {
        if (!is_null($this->getSocialId())) {
            $login = 'ok' . $this->getSocialId();
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
            $email = $login . '@ok.com';
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
            'auth_url' => 'http://www.odnoklassniki.ru/oauth/authorize',
            'auth_params' => array(
                'client_id' => $this->clientId,
                'response_type' => 'code',
                'redirect_uri' => $this->redirectUri
            )
        );
    }
}