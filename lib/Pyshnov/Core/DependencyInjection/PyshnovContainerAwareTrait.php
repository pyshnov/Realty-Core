<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\DependencyInjection;


use Pyshnov\Core\Config\ConfigInterface;
use Pyshnov\Core\Session\SessionInterface;
use Pyshnov\Core\Template\ErrorMessage;
use Symfony\Component\HttpFoundation\Request;

trait PyshnovContainerAwareTrait
{
    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->getContainer()->get($name);
    }

    /**
     * @return Request
     */
    public function request():Request
    {
        return $this->get('request_stack')->getCurrentRequest();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getParam()
    {
        return $this->request()->query;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function postParam()
    {
        return $this->request()->request;
    }

    /**
     * @return SessionInterface
     */
    public function session():SessionInterface
    {
        return $this->get('session');
    }

    /**
     * @return ConfigInterface
     */
    public function config()
    {
        return $this->get('config');
    }

    /**
     * @return ErrorMessage
     */
    public function error()
    {
        return $this->get('error_massage');
    }
}