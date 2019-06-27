<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\DB;


class DropTable extends Container
{
    protected $remove;
    protected $exists;

    public function __construct($db, $table)
    {
        parent::__construct($db);

        $this->setTable($table);
        $this->exists = false;
    }

    /**
     * @param bool $exists
     * @return $this
     */
    public function setExists($exists = true)
    {
        $this->exists = $exists;

        return $this;
    }

    protected function getDrop()
    {
        if ($this->exists) {
            return 'DROP TABLE IF EXISTS';
        }

        return 'DROP TABLE';
    }

    public function __toString()
    {
        $sql = $this->getDrop() . ' ' . $this->table;

        return $sql;
    }
}