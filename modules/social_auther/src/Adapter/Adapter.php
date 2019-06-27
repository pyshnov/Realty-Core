<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\social_auther\Adapter;


abstract class Adapter implements AdapterInterface {
    /**
     * Social Client ID
     *
     * @var string null
     */
    protected $clientId = null;
    /**
     * Social Client Secret
     *
     * @var string null
     */
    protected $clientSecret = null;
    /**
     * Social Redirect Uri
     *
     * @var string null
     */
    protected $redirectUri = null;
    /**
     * Name of auth provider
     *
     * @var string null
     */
    protected $provider = null;
    /**
     * Social Fields Map for universal keys
     *
     * @var array
     */
    protected $socialFieldsMap = array();
    /**
     * Storage for user info
     *
     * @var array
     */
    protected $userInfo = null;

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getSocialId()
    {
        $result = null;
        if (isset($this->userInfo[$this->socialFieldsMap['socialId']])) {
            $result = $this->userInfo[$this->socialFieldsMap['socialId']];
        }
        return $result;
    }
    /**
     * Get user email or null if it is not set
     *
     * @return string|null
     */
    public function getEmail()
    {
        $result = null;
        if (isset($this->userInfo[$this->socialFieldsMap['email']])) {
            $result = $this->userInfo[$this->socialFieldsMap['email']];
        }
        return $result;
    }
    /**
     * Get user name or null if it is not set
     *
     * @return string|null
     */
    public function getName()
    {
        $result = null;
        if (isset($this->userInfo[$this->socialFieldsMap['name']])) {
            $result = $this->userInfo[$this->socialFieldsMap['name']];
        }
        return $result;
    }
    /**
     * Get user social page url or null if it is not set
     * @return string|null
     */
    public function getSocialPage()
    {
        $result = null;
        if (isset($this->userInfo[$this->socialFieldsMap['socialPage']])) {
            $result = $this->userInfo[$this->socialFieldsMap['socialPage']];
        }
        return $result;
    }
    /**
     * Get url of user's avatar or null if it is not set
     *
     * @return string|null
     */
    public function getAvatar()
    {
        $result = null;
        if (isset($this->userInfo[$this->socialFieldsMap['avatar']])) {
            $result = $this->userInfo[$this->socialFieldsMap['avatar']];
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
        if (isset($this->userInfo[$this->socialFieldsMap['sex']])) {
            $result = $this->userInfo[$this->socialFieldsMap['sex']];
        }
        return $result;
    }
    /**
     * Get user birthday in format dd.mm.YYYY or null if it is not set
     *
     * @return string|null
     */
    public function getBirthday()
    {
        $result = null;
        if (isset($this->userInfo[$this->socialFieldsMap['birthday']])) {
            $result = date('d.m.Y', strtotime($this->userInfo[$this->socialFieldsMap['birthday']]));
        }
        return $result;
    }
    /**
     * Return name of auth provider
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }
    /**
     * Get authentication url
     *
     * @return string
     */
    public function getAuthUrl()
    {
        $config = $this->prepareAuthParams();
        return $result = $config['auth_url'] . '?' . urldecode(http_build_query($config['auth_params']));
    }
    /**
     * Make post request and return result
     *
     * @param string $url
     * @param string $params
     * @param bool $parse
     * @return array|string
     */
    protected function post($url, $params, $parse = true)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        curl_close($curl);
        if ($parse) {
            $result = json_decode($result, true);
        }
        return $result;
    }
    /**
     * Make get request and return result
     *
     * @param $url
     * @param $params
     * @param bool $parse
     * @return mixed
     */
    protected function get($url, $params, $parse = true)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . '?' . urldecode(http_build_query($params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        curl_close($curl);
        if ($parse) {
            $result = json_decode($result, true);
        }
        return $result;
    }
}