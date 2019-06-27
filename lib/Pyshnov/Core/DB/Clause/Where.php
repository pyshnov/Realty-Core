<?php

namespace Pyshnov\Core\DB\Clause;


class Where
{
    /**
     * @var array
     */
    protected $container = [];

    /**
     * @param $column
     * @param string $operator
     * @param string $chainType
     */
    public function where($column, $operator, $chainType = 'AND')
    {
        if($operator === null) {
            $this->container[] = ' '.$chainType.' '.$column;
        } else {
            $this->container[] = ' '.$chainType.' '.$column.' '.$operator.' ?';

        }

    }

    /**
     *
     * @param $column
     * @param $operator
     */
    public function orWhere($column, $operator)
    {
        $this->where($column, $operator, 'OR');
    }

    /**
     * @param array $columns
     * @param null $operator
     * @param string $chainType
     */
    public function multiWhere(array $columns, $operator = null, $chainType = 'AND')
    {
        if($operator === null)
            $operator = '=';

        foreach ($columns as $column) {

            $this->container[] = ' '.$chainType.' '.$column.' '.$operator.' ?';
        }
    }

    /**
     * @param $column
     * @param $values
     * @param string $chainType
     * @param bool   $not
     */
    public function whereIn($column, $values, $chainType = 'AND', $not = false)
    {
        $syntax = 'IN';
        if ($not) {
            $syntax = 'NOT IN';
        }
        $this->container[] = ' '.$chainType.' '.$column.' '.$syntax.' '.$values;
    }

    /**
     * @param $column
     * @param string $chainType
     * @param bool   $not
     */
    public function whereLike($column, $chainType = 'AND', $not = false)
    {
        $syntax = 'LIKE';
        if ($not) {
            $syntax = 'NOT LIKE';
        }
        $this->container[] = ' '.$chainType.' '.$column.' '.$syntax.' ?';
    }

    /**
     * @return string
     */
    public function __toString()
    {

        if(empty($this->container))
            return '';

        $args = [];

        foreach ($this->container as $where) {
            $args[] = $where;
        }

        return ' WHERE '.ltrim(implode('', $args), ' AND');
    }

}