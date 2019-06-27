<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Session;

use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Session\Session as SymfonySession;


class Session extends SymfonySession implements SessionInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(Request $request)
    {
        $this->storage->start();

        $this->set('key', md5(rand().time()));

        $request->setSession($this);
    }
}