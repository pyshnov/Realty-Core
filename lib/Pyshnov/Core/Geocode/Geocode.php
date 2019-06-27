<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Geocode;


class Geocode
{
    /** дом */
    const KIND_HOUSE = 'house';
    /** улица */
    const KIND_STREET = 'street';
    /** станция метро */
    const KIND_METRO = 'metro';
    /** район города */
    const KIND_DISTRICT = 'district';
    /** населенный пункт (город/поселок/деревня/село/...) */
    const KIND_LOCALITY = 'locality';

    const LANG_RU = 'ru-RU';
    const LANG_US = 'en-US';

    protected $_query = [];

    protected $_data;

    protected $_list = [];

    public function __construct()
    {
        $this->clear();
    }

    public function load()
    {

        $url = sprintf('https://geocode-maps.yandex.ru/1.x/?%s', http_build_query($this->_query));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        $data = curl_exec($ch);
        //$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($data, true);

        $this->setData($data);

        return $this;
    }

    /**
     * Очистка фильтров гео-кодирования
     *
     * @return self
     */
    public function clear()
    {
        $this->_query = [
            'format' => 'json'
        ];
        // указываем явно значения по-умолчанию
        $this->setLang(self::LANG_RU)->setOffset(0)->setLimit(10);
        $this->_data = null;
        $this->_list = null;

        return $this;
    }

    /**
     * Запишем исходные данные
     *
     * @param $data
     */
    public function setData($data)
    {
        $this->_data = $data;

        if (isset($data['response']['GeoObjectCollection']['featureMember'])) {
            foreach ($data['response']['GeoObjectCollection']['featureMember'] as $item) {
                $this->_list[] = $this->geoObject($item['GeoObject']);
            }
        }
    }

    public function geoObject($geoData)
    {
        $data = [
            'Address' => $geoData['metaDataProperty']['GeocoderMetaData']['text'],
            'Kind' => $geoData['metaDataProperty']['GeocoderMetaData']['kind']
        ];
        array_walk_recursive(
            $geoData,
            function($value, $key) use (&$data) {
                if (in_array(
                    $key,
                    [
                        'CountryName',
                        'CountryNameCode',
                        'AdministrativeAreaName',
                        'SubAdministrativeAreaName',
                        'LocalityName',
                        'DependentLocalityName',
                        'ThoroughfareName',
                        'PremiseNumber',
                        'PremiseName',
                    ]
                )) {
                    $data[$key] = $value;
                }
            }
        );
        if (isset($geoData['Point']['pos'])) {
            $pos = explode(' ', $geoData['Point']['pos']);
            $data['Longitude'] = (float)$pos[0];
            $data['Latitude'] = (float)$pos[1];
        }

        return $data;
    }

    /**
     * Гео-кодирование по запросу (адрес/координаты)
     *
     * @param string $query
     * @return self
     */
    public function setQuery($query)
    {
        $this->_query['geocode'] = (string)$query;

        return $this;
    }

    /**
     * Гео-кодирование по координатам
     *
     * @see http://api.yandex.ru/maps/doc/geocoder/desc/concepts/input_params.xml#geocode-format
     * @param float $longitude Долгота в градусах
     * @param float $latitude Широта в градусах
     * @return self
     */
    public function setPoint($longitude, $latitude)
    {
        $longitude = (float)$longitude;
        $latitude = (float)$latitude;
        $this->_query['geocode'] = sprintf('%F,%F', $longitude, $latitude);

        return $this;
    }


    /**
     * Максимальное количество возвращаемых объектов (по-умолчанию 10)
     *
     * @param int $limit
     * @return self
     */
    public function setLimit($limit)
    {
        $this->_query['results'] = (int)$limit;

        return $this;
    }

    /**
     * Количество объектов в ответе (начиная с первого), которое необходимо пропустить
     *
     * @param int $offset
     * @return self
     */
    public function setOffset($offset)
    {
        $this->_query['skip'] = (int)$offset;

        return $this;
    }


    /**
     * Предпочитаемый язык описания объектов
     *
     * @param string $lang
     * @return self
     */
    public function setLang($lang)
    {
        $this->_query['lang'] = (string)$lang;

        return $this;
    }

    /**
     * Вид топонима (только для обратного геокодирования)
     *
     * @param string $kind
     * @return self
     */
    public function setKind($kind)
    {
        $this->_query['kind'] = (string)$kind;

        return $this;
    }


    /**
     * Исходные данные
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param null $number номер элемента, есл не указано вернет весь список
     * @return array|mixed
     */
    public function getList($number = null)
    {
        if ($number !== null)
            return $this->_list[$number];

        return $this->_list;
    }

    /**
     * Вернет первый объект
     *
     * @return mixed|null
     */
    public function getFirst()
    {
        $result = null;
        if (count($this->_list)) {
            $result = $this->_list[0];
        }

        return $result;
    }

    /**
     * Возвращает исходный запрос
     *
     * @return string|null
     */
    public function getQuery()
    {
        $result = null;
        if (isset($this->_data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['request'])) {
            $result = $this->_data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['request'];
        }

        return $result;
    }

    /**
     * Кол-во найденных результатов
     *
     * @return int
     */
    public function getFoundCount()
    {
        $result = null;
        if (isset($this->_data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'])) {
            $result = (int)$this->_data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'];
        }

        return $result;
    }

    public function getAddress($number = 0)
    {
        $result = null;
        if (isset($this->_list[$number]['Address']))
            $result = (string)$this->_list[$number]['Address'];

        return $result;
    }

    /**
     * Широта в градусах. Имеет десятичное представление с точностью до семи знаков после запятой
     *
     * @param int $number
     * @return float|null
     */
    public function getLatitude($number = 0)
    {
        $result = null;
        if (isset($this->_list[$number]['Latitude']))
            $result = (float)$this->_list[$number]['Latitude'];

        return $result;
    }

    /**
     * Долгота в градусах. Имеет десятичное представление с точностью до семи знаков после запятой
     *
     * @param $number
     * @return float|null
     */
    public function getLongitude($number = 0)
    {
        $result = null;
        if (isset($this->_list[$number]['Longitude']))
            $result = (float)$this->_list[$number]['Longitude'];

        return $result;
    }
}