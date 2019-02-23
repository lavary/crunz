<?php

declare(strict_types=1);

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
    public function normal($message): void
    {
        $this->write($message, self::VERBOSITY_NORMAL);
    }

    /**
     * @param string $message
     */
    public function verbose($message): void
    {
        $this->write($message, self::VERBOSITY_VERBOSE);
    }

    /**
     * @param string $message
     */
    public function veryVerbose($message): void
    {
        $this->write($message, self::VERBOSITY_VERY_VERBOSE);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     */
    public function debug($message): void
    {
        $this->write($message, self::VERBOSITY_DEBUG);
    }

    /**
     * @param string $message
     * @param int    $verbosity
     */
    private function write($message, $verbosity): void
    {
        $ioVerbosity = $this->symfonyStyle
            ->getVerbosity();

        if ($ioVerbosity >= $verbosity) {
            $this->symfonyStyle
                ->writeln($message);
        }
    }
}
