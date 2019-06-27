<?php

namespace Pyshnov\Core\DB\Clause;


class Order
{

    /**
     * @var array
     */
    protected $container = [];

    /**
     * @param $column
     * @param string $direction
     */
    public function orderBy($column, $direction = 'ASC')
    {
        if(stripos($column, 'DESC') || stripos($column, 'ASC'))
            $this->container[] = $column;
        else
            $this->container[] = $column.' '.strtoupper($direction);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (empty($this->container)) {
            return '';
        }

        return ' ORDER BY ' . implode(', ', $this->container);
    }

}