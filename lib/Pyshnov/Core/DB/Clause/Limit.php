<?php

namespace Pyshnov\Core\DB\Clause;


class Limit
{

    /**
     * @var null
     */
    private $limit;

    /**
     * @param $number
     * @param null $offset
     */
    public function limit($number, $offset = null)
    {
        if (is_int($number)) {
            if (is_int($offset) && $offset >= 0) {
                $this->limit = intval($offset).' , '.intval($number);
            } elseif ($number >= 0) {
                $this->limit = intval($number);
            }
        }
    }

    public function __toString()
    {
        if (is_null($this->limit)) {
            return '';
        }
        return ' LIMIT '.$this->limit;
    }

}