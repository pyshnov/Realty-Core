<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\DependencyInjection\Exception;


use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    private $id;
    private $sourceId;

    public function __construct($id, $sourceId = null, \Exception $previous = null, array $alternatives = array())
    {
        if (null === $sourceId) {
            $msg = sprintf('Вы запросили несуществующую сервис "%s".', $id);
        } else {
            $msg = sprintf('Сервис "%s" имееет зависимость от несуществующего сервиса "%s".', $sourceId, $id);
        }

        if ($alternatives) {
            if (1 == count($alternatives)) {
                $msg .= ' Вы имели в виду это: "';
            } else {
                $msg .= ' Вы имели в виду один из этих: "';
            }
            $msg .= implode('", "', $alternatives).'"?';
        }

        parent::__construct($msg, 0, $previous);

        $this->id = $id;
        $this->sourceId = $sourceId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSourceId()
    {
        return $this->sourceId;
    }
}