<?php

namespace Pyshnov\Core\DB;


use Pyshnov\Core\DB\Clause\Group;
use Pyshnov\Core\DB\Clause\Having;
use Pyshnov\Core\DB\Clause\Join;
use Pyshnov\Core\DB\Clause\Offset;
use Pyshnov\Core\DB\Clause\Order;

class Select extends Container
{

    /**
     * если true тогда будет производится исключения дубликатов из результатов команды SELECT
     *
     * @var bool
     */
    protected $distinct = false;

    /**
     * @var
     */
    protected $join;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var Having
     */
    protected $having;

    /**
     * @var Offset
     */
    protected $offset;

    /**
     * @param $db
     * @param $table
     * @param $columns
     */
    public function __construct($db, $columns, $table)
    {
        parent::__construct($db);

        $this->setColumns($columns);

        $this->setTable($table);

        $this->join = new Join();
        $this->group = new Group();
        $this->having = new Having();
        $this->order = new Order();
        $this->offset = new Offset();

    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (empty($this->table)) {
            trigger_error('Не указана таблица', E_USER_ERROR);
        }

        $sql = $this->getSelect() . ' ' . $this->getColumns();
        $sql .= ' FROM ' . $this->table;
        $sql .= $this->join;
        $sql .= $this->where;
        $sql .= $this->group;
        $sql .= $this->having;
        $sql .= $this->order;
        $sql .= $this->limit;
        $sql .= $this->offset;

        return $sql;
    }

    /**
     * @return string
     */
    protected function getSelect()
    {
        if ($this->distinct) {
            return 'SELECT DISTINCT';
        }

        return 'SELECT';
    }

    /**
     * @return string
     */
    protected function getColumns()
    {
        return implode(', ', $this->columns);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        return parent::execute();
    }

    /**
     * @param        $table
     * @param        $first
     * @param null   $operator
     * @param null   $second
     * @param string $joinType
     * @return $this
     */
    public function join($table, $first, $operator = null, $second = null, $joinType = 'INNER')
    {
        $this->join->join($table, $first, $operator, $second, $joinType);

        return $this;
    }

    /**
     * @param string      $table
     * @param string      $first
     * @param string|null $operator
     * @param mixed       $second
     * @param string      $joinType
     * @return $this
     */
    public function leftJoin($table, $first, $operator = null, $second = null, $joinType = 'LEFT OUTER')
    {
        $this->join->join($table, $first, $operator, $second, $joinType);

        return $this;
    }

    /**
     * @param string      $table
     * @param string      $first
     * @param string|null $operator
     * @param mixed       $second
     * @param string      $joinType
     * @return $this
     */
    public function rightJoin($table, $first, $operator = null, $second = null, $joinType = 'RIGHT OUTER')
    {
        $this->join->join($table, $first, $operator, $second, $joinType);

        return $this;
    }

    /**
     * @param      $table
     * @param      $first
     * @param null $operator
     * @param null $second
     * @return $this
     */
    public function fullJoin($table, $first, $operator = null, $second = null)
    {
        $this->join->join($table, $first, $operator, $second, 'FULL');

        return $this;
    }

    /**
     * @param string $column
     * @param null   $as
     * @param bool   $distinct - используется для указания на то, что следует работать только с уникальными значениями
     *     столбца.
     * @return $this
     */
    public function count($column = '*', $as = null, $distinct = false)
    {
        $this->columns[] = ($distinct ? 'COUNT( DISTINCT' : 'COUNT(') . ' ' . $column . ' )' . $this->setAs($as);

        return $this;
    }

    /**
     * @param      $column
     * @param null $as
     * @return $this
     */
    public function max($column, $as = null)
    {
        $this->columns[] = 'MAX( ' . $column . ' )' . $this->setAs($as);

        return $this;
    }

    /**
     * @param      $column
     * @param null $as
     * @return $this
     */
    public function min($column, $as = null)
    {
        $this->columns[] = 'MIN( ' . $column . ' )' . $this->setAs($as);

        return $this;
    }

    /**
     * Вернет среднее значение стобцов
     *
     * @param      $column
     * @param null $as
     * @return $this
     */
    public function avg($column, $as = null)
    {
        $this->columns[] = 'AVG( ' . $column . ' )' . $this->setAs($as);

        return $this;
    }

    /**
     * Сумма значений
     *
     * @param      $column
     * @param null $as
     * @return $this
     */
    public function sum($column, $as = null)
    {
        $this->columns[] = 'SUM( ' . $column . ' )' . $this->setAs($as);

        return $this;
    }

    /**
     * Выполняет сортировку выходных значений
     *
     * @param        $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->order->orderBy($column, $direction);

        return $this;
    }

    /**
     * Объединеняет результатов выборки
     *
     * @param $columns
     * @return $this
     */
    public function groupBy($columns)
    {
        $this->group->groupBy($columns);

        return $this;
    }

    public function having($column, $operator = null, $value = null, $chainType = 'AND')
    {
        $this->values[] = $value;
        $this->having->having($column, $operator, $chainType);

        return $this;
    }

    public function orHaving($column, $operator = null, $value = null)
    {
        $this->values[] = $value;
        $this->having->orHaving($column, $operator);

        return $this;
    }

    public function havingMax($column, $operator = null, $value = null)
    {
        $this->values[] = $value;
        $this->having->havingMax($column, $operator);

        return $this;
    }

    public function havingMin($column, $operator = null, $value = null)
    {
        $this->values[] = $value;
        $this->having->havingMin($column, $operator);

        return $this;
    }

    public function havingCount($column, $operator = null, $value = null)
    {
        $this->values[] = $value;
        $this->having->havingCount($column, $operator);

        return $this;
    }

    public function havingAvg($column, $operator = null, $value = null)
    {
        $this->values[] = $value;
        $this->having->havingAvg($column, $operator);

        return $this;
    }

    public function havingSum($column, $operator = null, $value = null)
    {
        $this->values[] = $value;
        $this->having->havingSum($column, $operator);

        return $this;
    }

    public function offset($number)
    {
        $this->offset->offset($number);

        return $this;
    }

    /**
     * @param $as
     * @return string
     */
    protected function setAs($as)
    {
        if (empty($as)) {
            return '';
        }

        return ' AS ' . $as;
    }


}