<?php

namespace Pyshnov\Core\DB;

use Pyshnov\Core\DB\Clause\Limit;
use Pyshnov\Core\DB\Clause\Where;

abstract class Container
{
    /**
     * @var DB
     */
    public $db;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var
     */
    protected $table;

    /**
     * @var object Where
     */
    protected $where;

    /**
     * @var object Limit
     */
    protected $limit;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $placeholders = [];

    public function __construct($db)
    {
        $this->db = $db;

        $this->where = new Where();
        $this->limit = new Limit();
    }

    /**
     * Запишет что выбираем
     *
     * @param $columns
     * @return $this
     */
    protected function setColumns($columns)
    {
        if ($columns === '')
            $columns = ['*'];
        else
            if (is_string($columns))
                $columns = explode(',', $columns);

        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    /**
     * Запишет имя таблицы с которой будем работать
     *
     * @param $table
     * @return $this
     */
    protected function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Ищет соответствие по одному параметру
     * аналог WHERE usr = ?
     *
     * @param string $column
     * @param string $operator
     * @param        $value
     * @param        $chainType
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $chainType = 'AND')
    {
        if ($column == '')
            return $this;

        if ($value !== null)
            $this->values[] = $value;

        $this->where->where($column, $operator, $chainType);

        return $this;
    }

    /**
     * Применяется совместно с where(), добавляем или
     * аналог WHERE usr = ? OR f_name = ?
     *
     * @param        $column
     * @param string $operator
     * @param string $value
     * @return $this
     */
    public function orWhere($column, $operator, $value)
    {
        $this->values[] = $value;

        $this->where->orWhere($column, $operator);

        return $this;
    }

    /**
     *
     * аналог WHERE col_1 = ? AND col_2 = ? AND col_3 = ?
     *
     * @param        $columns
     * @param null   $operator
     * @param string $chainType
     * @return $this
     */
    public function multiWhere(array $columns, $operator = null, $chainType = 'AND')
    {
        $this->values = array_merge($this->values, array_values($columns));
        $this->where->multiWhere(array_keys($columns), $operator, $chainType);

        return $this;
    }

    /**
     * @param        $column
     * @param array  $values
     * @param string $chainType
     * @param bool   $not
     * @return $this
     */
    public function whereIn($column, array $values = null, $chainType = 'AND', $not = false)
    {
        if ($values === null)
            return $this;

        $this->setValues($values);
        $this->setPlaceholders($values);
        $this->where->whereIn($column, $this->getPlaceholders(), $chainType, $not);

        return $this;
    }

    /**
     * @param        $column
     * @param        $value
     * @param string $chainType
     * @param        $not
     * @return $this
     */
    public function whereLike($column, $value, $chainType = 'AND', $not = false)
    {
        $this->values[] = $value;
        $this->where->whereLike($column, $chainType, $not);

        return $this;
    }

    /**
     * @return mixed
     */
    abstract public function __toString();

    public function execute()
    {

        DB::$countCalls++;

        $stmt = $this->getStatement(); // prepare()

        $stmt->execute($this->values);

        return $stmt;
    }

    /**
     * @return mixed
     */
    public function getStatement()
    {
        return $this->db->prepare($this);
    }

    /**
     * @param array $values
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = array_merge($this->values, $values);
        return $this;
    }

    /**
     * @param array $values
     */
    protected function setPlaceholders(array $values)
    {
        $count = count($values);

        for ($x = 0; $x < $count; $x++) {
            $this->placeholders[] = '?';
        }
    }

    /**
     * @return string
     */
    protected function getPlaceholders()
    {
        return '(' . implode(', ', $this->placeholders) . ')';
    }

    /**
     * @param $number
     * @param $offset
     * @return $this
     */
    public function limit($number, $offset = null)
    {

        $this->limit->limit((int)$number, (int)$offset);

        return $this;
    }


    /**
     * Нужно будет реализоваеть очиску памяти от параметров запроса (table, where и пр.)
     */
    public function reset()
    {
    }


}