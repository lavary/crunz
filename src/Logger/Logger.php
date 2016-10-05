<?php

namespace Crunz\Logger;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Crunz\Configuration\Configurable;
use Crunz\Singleton;

class Logger extends Singleton {
    
    use Configurable;

    /**
     * Instance of Psr\Log\LoggerInterface
     *
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * The Log levels.
     *
     * @var array
     */
    protected $levels = [
        
        'debug'     => MonologLogger::DEBUG,
        'info'      => MonologLogger::INFO,
        'notice'    => MonologLogger::NOTICE,
        'warning'   => MonologLogger::WARNING,
        'error'     => MonologLogger::ERROR,
        'critical'  => MonologLogger::CRITICAL,
        'alert'     => MonologLogger::ALERT,
        'emergency' => MonologLogger::EMERGENCY,
        
    ];

    /**
     * Initialize the logger instance 
     *
     * @param \Monolog\Logger $logger
     */
    public function __construct(\Monolog\Logger $logger)
    {
        $this->configurable();
        
        $this->logger = $logger;
    }

    /**
     * Create a neaw stream handler
     *
     * @param string  $path
     * @param int     $level
     * @param Boolean $bubble
     *
     * @return \Monolog\Handler\StreamHandler
     */
    public function addStream($path, $level, $bubble = true)
    {        
        $this->logger->pushHandler($handler = new StreamHandler($path, $this->parseLevel($level), $bubble));
        $handler->setFormatter($this->getDefaultFormatter()); 

        return $this;  
    }

    /**
     * Get a default Monolog formatter instance
     *
     * @return \Monolog\Formatter\LineFormatter
     */
    protected function getDefaultFormatter()
    {
        return new LineFormatter(null, null, false, false);
    }

    /**
     * Log any output if output logging is enabled
     *
     * @param  string $content
     *
     * @return Boolean
     */
    public function info($content)
    {
        return $this->write($content, 'info');
    }

    /**
     * Log  the error is error logging is enabled.
     *
     * @param  string $message
     *
     * @return Boolean
     */
    public function error($message)
    {    
        return $this->write($message, 'error');
    }

    /**
     * Write the log to the specified stream
     *
     * @param  string $content
     * @param  string $level
     *
     * @return mixed
     */
     public function write($content, $level)
    {    
        return $this->logger->{$level}($content);
    }

    /**
     * Parse the string level into a Monolog constant.
     *
     * @param  string  $level
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    protected function parseLevel($level)
    {
        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        throw new InvalidArgumentException('Invalid log level.');
    }

}