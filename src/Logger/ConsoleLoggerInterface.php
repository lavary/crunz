<?php

namespace Crunz\Logger;

use Symfony\Component\Console\Output\OutputInterface;

interface ConsoleLoggerInterface
{
    const VERBOSITY_QUIET = OutputInterface::VERBOSITY_QUIET;
    const VERBOSITY_NORMAL = OutputInterface::VERBOSITY_NORMAL;
    const VERBOSITY_VERBOSE = OutputInterface::VERBOSITY_VERBOSE;
    const VERBOSITY_VERY_VERBOSE = OutputInterface::VERBOSITY_VERY_VERBOSE;
    const VERBOSITY_DEBUG = OutputInterface::VERBOSITY_DEBUG;

    /**
     * @param string $message
     */
    public function normal($message);

    /**
     * @param string $message
     */
    public function verbose($message);

    /**
     * @param string $message
     */
    public function veryVerbose($message);

    /**
     * Detailed debug information.
     *
     * @param string $message
     */
    public function debug($message);
}
