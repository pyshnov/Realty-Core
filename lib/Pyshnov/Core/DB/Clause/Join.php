<?php

namespace Pyshnov\Core\DB\Clause;


class Join
{
    /**
     * @var array
     */
    protected $container = [];

    /**
     * @param $table
     * @param $first
     * @param null   $operator
     * @param null   $second
     * @param string $joinType
     */
    public function join($table, $first, $operator = null, $second = null, $joinType = 'INNER')
    {
        $this->container[] = ' '.$joinType.' JOIN '.$table.' ON '.$first.' '.$operator.' '.$second;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (empty($this->container)) {
            return '';
        }

        $args = [];

        foreach ($this->container as $join) {
            $args[] = $join;
        }

        return implode('', $args);
    }

}