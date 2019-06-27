<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\social_auther\Adapter;


class Facebook extends Adapter {

    public function __construct($config)
    {
        parent::__construct($config);
        $this->socialFieldsMap = array(
            'socialId'   => 'id',
            'email'      => 'email',
            'name'       => 'name',
            'socialPage' => 'link',
            'sex'        => 'gender',
            'birthday'   => 'birthday'
        );
        $this->provider = 'facebook';
    }
    /**
     * Get url of user's avatar or null if it is not set
     *
     * @return string|null
     */
    public function getAvatar()
    {
        $result = null;
        if (isset($this->userInfo['username'])) {
            $result = 'http://graph.facebook.com/' . $this->userInfo['username'] . '/picture?type=large';
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
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUri,
                'client_secret' => $this->clientSecret,
                'code'          => $_GET['code']
            );
            parse_str($this->get('https://graph.facebook.com/oauth/access_token', $params, false), $tokenInfo);
            if (count($tokenInfo) > 0 && isset($tokenInfo['access_token'])) {
                $params = array('access_token' => $tokenInfo['access_token'], 'fields'=>'email,name');
                $userInfo = $this->get('https://graph.facebook.com/me', $params);
                if (isset($userInfo['id'])) {
                    $this->userInfo = $userInfo;
                    $result = true;
                }
            }
        }
        return $result;
    }
    /**
     * Prepare params for authentication url
     *
     * @return array
     */
    public function prepareAuthParams()
    {
        return array(
            'auth_url'    => 'https://www.facebook.com/dialog/oauth',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code',
                'scope'         => 'email,public_profile'
            )
        );
    }

}