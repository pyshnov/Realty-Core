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
 * Time: 2:55
 */

namespace Pyshnov\social_auther\Adapter;


interface AdapterInterface {
    /**
     * Authenticate and return bool result of authentication
     *
     * @return bool
     */
    public function authenticate();
}