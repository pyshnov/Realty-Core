<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Logger;


use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

class FileLogger extends AbstractLogger
{
    private $filePath;

    public function __construct($filePath = null)
    {
        $this->setFilePath($filePath);
    }


    /**
     * @param string $filePath
     * @return bool
     */
    public function setFilePath(string $filePath)
    {
        if (!file_exists(dirname($filePath)) || (!is_writable($filePath) && !is_writable(dirname($filePath))))
            return false;
        $this->filePath = $filePath;

        return true;
    }

    public function log($level, $message, array $context = [])
    {
        if (!in_array($level, [
                LogLevel::EMERGENCY,
                LogLevel::ALERT,
                LogLevel::CRITICAL,
                LogLevel::ERROR,
                LogLevel::WARNING,
                LogLevel::NOTICE,
                LogLevel::INFO,
                LogLevel::DEBUG
            ]
        ))
            throw new InvalidArgumentException('Invalid or unsupported log level ' . $level);

        if (is_null($this->filePath))
            return false;

        $buf = '';
        $buf .= date('r');
        $buf .= ' - ';
        if (isset($_SERVER) && is_array($_SERVER) && array_key_exists('REMOTE_ADDR', $_SERVER))
            $buf .= $_SERVER['REMOTE_ADDR'];
        else
            $buf .= '~';
        $buf .= ' - ' . php_uname('n');
        $buf .= ' - ' . strtoupper($level);
        $buf .= ' - ' . $this->interpolate($message, $context);
        file_put_contents($this->filePath, $buf . PHP_EOL, FILE_APPEND);

        return true;
    }

    private function interpolate($message, array $context = [])
    {
        $replace = [];
        foreach ($context as $k => $v)
            $replace['{' . $k . '}'] = $v;

        return strtr((string)$message, $replace);
    }
}