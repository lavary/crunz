<?php

declare(strict_types=1);

namespace Crunz\Tests\TestCase\Logger;

use Psr\Log\AbstractLogger;

final class SpyPsrLogger extends AbstractLogger
{
    /** @var array<int,array> */
    private $logs = [];

    public function log($level, $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }

    /** @var array<int,array> */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
