<?php

declare(strict_types=1);

namespace Crunz\Logger;

use Symfony\Component\Console\Output\OutputInterface;

interface ConsoleLoggerInterface
{
    public const VERBOSITY_QUIET = OutputInterface::VERBOSITY_QUIET;
    public const VERBOSITY_NORMAL = OutputInterface::VERBOSITY_NORMAL;
    public const VERBOSITY_VERBOSE = OutputInterface::VERBOSITY_VERBOSE;
    public const VERBOSITY_VERY_VERBOSE = OutputInterface::VERBOSITY_VERY_VERBOSE;
    public const VERBOSITY_DEBUG = OutputInterface::VERBOSITY_DEBUG;

    /**
     * @param string $message
     */
    public function normal($message): void;

    /**
     * @param string $message
     */
    public function verbose($message): void;

    /**
     * @param string $message
     */
    public function veryVerbose($message): void;

    /**
     * Detailed debug information.
     *
     * @param string $message
     */
    public function debug($message): void;
}
