<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\location;


class CityCase
{
    protected $case = [
        '0' => 'i',
        '1' => 'r',
        '2' => 'd',
        '3' => 'v',
        '4' => 't',
        '5' => 'p'
    ];

    private $data;

    public function __construct(array $data)
    {
        $this->setData($data);
    }

    private function setData($data)
    {
        foreach ($this->case as $key => $value) {
            $this->data[$value] = $data[(int)$key];
        }
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function get($key)
    {
        $key = $this->case[(string)$key] ?? $key;

        return $this->data[$key] ?? '';
    }
}