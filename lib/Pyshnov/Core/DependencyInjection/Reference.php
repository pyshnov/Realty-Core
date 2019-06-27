<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\DependencyInjection;


class Reference
{
    private $id;

    /**
     * @param string $id              The service identifier
     *
     * @see Container
     */
    public function __construct($id)
    {
        $this->id = (string) $id;
    }

    /**
     * @return string The service identifier
     */
    public function __toString()
    {
        return $this->id;
    }

}