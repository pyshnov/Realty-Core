<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Template;


class ErrorMessage
{
    /**
     * Массив сообщений об ошибках
     * @var array
     */
    protected $messages;

    /**
     * @param $messages string|array
     */
    public function set($messages)
    {
        if(is_array($messages)) {
            foreach ($messages as $message) {
                $this->add($message);
            }
        } else {
            $this->add($messages);
        }
    }

    /**
     * @param string $message
     */
    public function add(string $message)
    {
        $this->messages[] = $message;
    }

    /**
     * @return array
     */
    public function get():array
    {
        return $this->messages ?? [];
    }

    /**
     * @return array
     */
    public function all():array
    {
        return $this->messages ?? [];
    }

    /**
     * @return bool
     */
    public function has():bool
    {
        return !is_null($this->messages);
    }
}