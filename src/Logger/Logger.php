<?php

namespace Crunz\Logger;

use Crunz\Configuration\Configuration;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

class Logger
{
    /**
     * Instance of Psr\Log\LoggerInterface.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * The Log levels.
     *
     * @var array
     */
    protected $levels = [
        'debug' => MonologLogger::DEBUG,
        'info' => MonologLogger::INFO,
        'notice' => MonologLogger::NOTICE,
        'warning' => MonologLogger::WARNING,
        'error' => MonologLogger::ERROR,
        'critical' => MonologLogger::CRITICAL,
        'alert' => MonologLogger::ALERT,
        'emergency' => MonologLogger::EMERGENCY,
    ];
    /** @var Configuration */
    private $configuration;

    /**
     * Initialize the logger instance.
     *
     * @param \Monolog\Logger $logger
     */
    public function __construct(\Monolog\Logger $logger, Configuration $configuration)
    {
        $this->logger = $logger;
        $this->configuration = $configuration;
    }

    /**
     * Create a neaw stream handler.
     *
     * @param string $path
     * @param int    $level
     * @param bool   $bubble
     *
     * @return \Monolog\Handler\StreamHandler
     */
    public function addStream($path, $level, $bubble = true)
    {
        $handler = new StreamHandler(
            $path,
            $this->parseLevel($level),
            $bubble
        );
        $handler->setFormatter($this->getDefaultFormatter());

        $this->logger
            ->pushHandler($handler)
        ;

        return $this;
    }

    /**
     * Log any output if output logging is enabled.
     *
     * @param string $content
     *
     * @return bool
     */
    public function info($content)
    {
        return $this->write($content, 'info');
    }

    /**
     * Log  the error is error logging is enabled.
     *
     * @param string $message
     *
     * @return bool
     */
    public function error($message)
    {
        return $this->write($message, 'error');
    }

    /**
     * Write the log to the specified stream.
     *
     * @param string $content
     * @param string $level
     *
     * @return mixed
     */
    public function write($content, $level)
    {
        return $this->logger->{$level}($content);
    }

    /**
     * Get a default Monolog formatter instance.
     *
     * @return \Monolog\Formatter\LineFormatter
     */
    protected function getDefaultFormatter()
    {
        $allowLinebreaks = $this->configuration
            ->get('log_allow_line_breaks')
        ;

        return new LineFormatter(
            null,
            null,
            $allowLinebreaks,
            false
        );
    }

    /**
     * Parse the string level into a Monolog constant.
     *
     * @param string $level
     *
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
