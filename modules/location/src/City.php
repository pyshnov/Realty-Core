<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\location;


use Pyshnov\Core\DB\DB;

class City
{
    protected $id;
    protected $name;
    protected $aliases;
    protected $regionId;
    protected $declension;

    protected $cities;

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->aliases;
    }

    /**
     * @param string $aliases
     *
     * @return $this
     */
    public function setAlias(string $aliases)
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * @param $regionId
     *
     * @return $this
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;

        return $this;
    }

    /**
     * @return CityCase
     */
    public function getDeclension(): CityCase
    {
        return $this->declension;
    }

    /**
     * @param CityCase $declension
     */
    public function setDeclension(CityCase $declension)
    {
        $this->declension = $declension;
    }

    /**
     * Вернет все активные города
     * Запишем результат в $this->cities
     * чтобы каждый раз не обращаться к базе
     *
     * @return array
     */
    public function getAll()
    {
        if(is_null($this->cities)) {
            $query = DB::select('*', DB_PREFIX . '_city')
                ->where('active', '=', 1)
                ->execute()
                ->fetchAll();

            if($query) {
                $this->cities = $query;
            } else {
                $this->cities = [];
            }
        }

        return $this->cities;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function getCityById($id)
    {
        $query = DB::select('*', DB_PREFIX . '_city')
            ->where('city_id', '=', $id)
            ->execute()
            ->fetch();

        if($query) {
            return $query;
        }
        return false;
    }
}