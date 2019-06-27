<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

/**
 * Created by PhpStorm.
 * User: aleksandr
 * Date: 16.05.17
 * Time: 20:38
 */

namespace Pyshnov\Core\DB\Clause;


class Group
{
    /**
     * @var array
     */
    protected $container = [];

    /**
     * @param $columns
     */
    public function groupBy($columns)
    {
        $this->container[] = $columns;
    }
    /**
     * @return string
     */
    public function __toString()
    {
        if (empty($this->container)) {
            return '';
        }
        return ' GROUP BY '.implode(' , ', $this->container);
    }
}