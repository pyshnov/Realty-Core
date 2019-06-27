<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\DB;


class Create extends Container
{
    protected $params;

    public function __construct($db, $columns, $table, $params)
    {
        parent::__construct($db);

        $this->setColumns($columns);

        $this->setTable($table);

        $this->setParams($params);
    }

    /**
     * @return string
     */
    protected function getColumns()
    {
        $str = '';

        foreach($this->columns as $column => $definition) {
            $str .= (!is_int($column) ? $column . ' ' : '') . $definition . ',';
        }
        
        return '('. rtrim($str, ',') . ')';
    }

    /**
     * @return string
     */
    public function getParams(): string
    {
        return $this->params ? ' ' . $this->params . ';' : '';
    }

    /**
     * @param string $params
     */
    public function setParams(string $params)
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        return parent::execute();
    }

    public function __toString()
    {
        if (empty($this->table)) {
            trigger_error('No table is set for update', E_USER_ERROR);
        }

        if (empty($this->columns) && empty($this->values)) {
            trigger_error('Missing columns and values for update', E_USER_ERROR);
        }

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->table;
        $sql .= ' '.$this->getColumns();
        $sql .= $this->getParams();

        return $sql;
    }
}