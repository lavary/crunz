<?php

namespace Crunz\Logger;

use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleLogger implements ConsoleLoggerInterface
{
    /** @var SymfonyStyle */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @param string $message
     */
    public function normal($message)
    {
        $this->write($message, self::VERBOSITY_NORMAL);
    }

    /**
     * @param string $message
     */
    public function verbose($message)
    {
        $this->write($message, self::VERBOSITY_VERBOSE);
    }

    /**
     * @param string $message
     */
    public function veryVerbose($message)
    {
        $this->write($message, self::VERBOSITY_VERY_VERBOSE);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     */
    public function debug($message)
    {
        $this->write($message, self::VERBOSITY_DEBUG);
    }

    /**
     * @param string $message
     * @param int    $verbosity
     */
    private function write($message, $verbosity)
    {
        $ioVerbosity = $this->symfonyStyle
            ->getVerbosity();

        if ($ioVerbosity >= $verbosity) {
            $this->symfonyStyle
                ->writeln($message);
        }
    }
}
