<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\DependencyInjection;


class Alias
{
    private $id;
    private $public;

    /**
     * @param string $id     Идентификатор псевдонима
     * @param bool   $public Если этот псевдоним является публичным
     */
    public function __construct($id, $public = true)
    {
        $this->id = (string) $id;
        $this->public = $public;
    }

    /**
     * Проверяет, должен ли этот псевдомим быть пубдичным
     *
     * @return bool
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * Устанавливает, является ли этот псевдоним публичным
     *
     * @param bool $boolean
     */
    public function setPublic(bool $boolean)
    {
        $this->public = $boolean;
    }

    /**
     * Возвращает идентификатор этого псевдонима.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }
}