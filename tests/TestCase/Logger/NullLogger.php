<?php

declare(strict_types=1);

namespace Crunz\Tests\TestCase\Logger;

use Crunz\Logger\ConsoleLoggerInterface;

final class NullLogger implements ConsoleLoggerInterface
{
    /** {@inheritdoc} */
    public function normal($message): void
    {
        // No-op
    }

    /** {@inheritdoc} */
    public function verbose($message): void
    {
        // No-op
    }

    /** {@inheritdoc} */
    public function veryVerbose($message): void
    {
        // No-op
    }

    /** {@inheritdoc} */
    public function debug($message): void
    {
        // No-op
    }
}
