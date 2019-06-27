<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\DB\Clause;


class Offset
{
    /**
     * @var null
     */
    private $offset = null;

    /**
     * @param $number
     */
    public function offset($number)
    {
        if (is_int($number) && $number >= 0) {
            $this->offset = intval($number);
        }
    }
    /**
     * @return string
     */
    public function __toString()
    {
        if (is_null($this->offset)) {
            return '';
        }
        return ' OFFSET '.$this->offset;
    }
}