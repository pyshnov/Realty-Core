<?php

namespace Pyshnov\Core\DB;


class Delete extends Container
{

    public function __construct($db, $table)
    {
        parent::__construct($db);

        $this->setTable($table);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (empty($this->table)) {
            trigger_error('No table is set for deletion', E_USER_ERROR);
        }

        $sql = 'DELETE FROM ' . $this->table;
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


}