<?php


namespace Pyshnov\Core\DB;


class DB extends \PDO
{
    protected static $_instance = null;
    private static $pdo = null;

    public static $countCalls = 0;

    function __construct($databases)
    {
        try {

            @parent::__construct($databases['db_dsn'], $databases['db_user'], $databases['db_pass'], $this->getDefaultOptions());
        } catch (\Exception $e) {

            echo 'Упс!!! Что то пошло не так!';

           // echo 'Database Connection Failed with Message: ' . $e->getMessage();

            die();
        }

    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, // устанавлеваем способ обработки ошибок
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, // тип получаемого результата по-умолчанию
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_PERSISTENT => true

        ];
    }

    /**
     * @param $columns
     * @param string $table
     * @return Select
     */
    public static function select($columns, $table)
    {
        return new Select(self::$_instance, $columns, $table);
    }

    public static function insert(array $columns = [], $table)
    {
        return new Insert(self::$_instance, $columns, $table);
    }

    /**
     * @param array $columns
     * @param $table
     * @return Update
     */
    public static function update(array $columns = [], $table)
    {
        return new Update(self::$_instance, $columns, $table);
    }

    /**
     * @param $table
     * @return Delete
     */
    public static function delete($table)
    {
        return new Delete(self::$_instance, $table);
    }

    /**
     * @param array $columns
     * @param $table
     * @param string $params
     * @return Create
     */
    public static function create(array $columns, $table, $params = '')
    {
        return new Create(self::$_instance, $columns, $table, $params);
    }

    public static function dropTable($table)
    {
        return new DropTable(self::$_instance, $table);
    }

    /**
     * Инициализируем единственный экземпляр данного классаю
     * @return object - объект класса DB
     */
    static public function getInstance($databases)
    {
        if(is_null(self::$_instance)) {
            self::$_instance = new DB($databases);
        }
        return self::$_instance;
    }

    /**
     * Инициализируем данный класс DB.
     */
    public static function init($databases)
    {
        self::getInstance($databases);
    }


}