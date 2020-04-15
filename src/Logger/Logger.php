<?php

declare(strict_types=1);

namespace Crunz\Logger;

use Psr\Log\LoggerInterface;

class Logger
{
    private $psrLogger;

    public function __construct(LoggerInterface $psrLogger)
    {
        $this->psrLogger = $psrLogger;
    }

    /**
     * Log any output if output logging is enabled.
     */
    public function info(string $message): void
    {
        $this->log($message, 'info');
    }

    /**
     * Log  the error is error logging is enabled.
     */
    public function error(string $message): void
    {
        $this->log($message, 'error');
    }

    private function log(string $content, string $level): void
    {
        $this->psrLogger
            ->log($level, $content)
        ;
    }
}
