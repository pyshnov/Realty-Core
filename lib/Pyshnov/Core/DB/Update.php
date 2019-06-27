<?php

namespace Pyshnov\Core\DB;


class Update extends Container
{

    public function __construct($db, array $columns, $table)
    {
        parent::__construct($db);

        $this->set($columns);

        $this->setTable($table);
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function set($columns)
    {
        foreach ($columns as $column => $value) {
            if(is_int($column)) {
                $this->columns[] = $value;
            } else {
                $this->columns[] = $column.' = ?';
                $this->values[] = $value;
            }
        }

        return $this;
    }

    public function __toString()
    {

        if (empty($this->table)) {
            trigger_error('No table is set for update', E_USER_ERROR);
        }

        if (empty($this->columns) && empty($this->values)) {
            trigger_error('Missing columns and values for update', E_USER_ERROR);
        }

        $sql = 'UPDATE '.$this->table;
        $sql .= ' SET '.$this->getColumns();
        $sql .= $this->where;

        return $sql;
    }

    /**
     * @return int
     */
    public function execute()
    {
        return parent::execute()->rowCount();
    }

    /**
     * @return string
     */
    protected function getColumns()
    {
        return implode(', ', $this->columns);
    }

}