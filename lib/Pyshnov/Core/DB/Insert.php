<?php

namespace Pyshnov\Core\DB;


class Insert extends Container
{
    protected $addPlaceholders;

    public function __construct($db, array $columns, $table)
    {
        parent::__construct($db);

        $this->columns($columns);

        $this->setTable($table);
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function columns($columns)
    {
        foreach ($columns as $column => $value) {
            $this->columns[] = $column;
            $this->values[] = $value;
            $this->placeholders[] = '?';
        }

        return $this;
    }

    /**
     * Добавит дополнительные значения для записи нескольких строк
     *
     * @param array $values
     * @return $this
     */
    public function addValues(array $values)
    {
        $val = [];
        foreach($values as $value) {
            $this->values[] =  $value;
            $val[] = '?';
        }

        $this->addPlaceholders[] = '('.implode(', ', $val).')';
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddPlaceholders()
    {
        return null !== $this->addPlaceholders ? ', ' . implode(', ', $this->addPlaceholders) : '';
    }


    public function __toString()
    {
        if (empty($this->table)) {
            trigger_error('No table is set for update', E_USER_ERROR);
        }

        if (empty($this->columns) && empty($this->values)) {
            trigger_error('Missing columns and values for update', E_USER_ERROR);
        }

        $sql = 'INSERT INTO '.$this->table;
        $sql .= ' '.$this->getColumns();
        $sql .= ' VALUES '.$this->getPlaceholders() . $this->getAddPlaceholders();

        return $sql;
    }

    /**
     * @param bool $insertId - если true, вернет id записи
     * @return string
     */
    public function execute($insertId = true)
    {
        if (!$insertId) {
            return parent::execute();
        }

        parent::execute();

        return $this->db->lastInsertId();
    }

    /**
     * @return string
     */
    protected function getColumns()
    {
        return '('.implode(', ', $this->columns).')';
    }

}