<?php

namespace Crunz\Tests\TestCase\Logger;

use Crunz\Logger\ConsoleLoggerInterface;

final class NullLogger implements ConsoleLoggerInterface
{
    /** {@inheritdoc} */
    public function normal($message)
    {
        // No-op
    }

    /** {@inheritdoc} */
    public function verbose($message)
    {
        // No-op
    }

    /** {@inheritdoc} */
    public function veryVerbose($message)
    {
        // No-op
    }

    /** {@inheritdoc} */
    public function debug($message)
    {
        // No-op
    }
}
