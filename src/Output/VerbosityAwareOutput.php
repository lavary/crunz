<?php

declare(strict_types=1);

namespace Crunz\Output;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @TODO Remove it in Crunz v2.
 */
class VerbosityAwareOutput
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function write($messages, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $commandVerbosity = $this->output
            ->getVerbosity()
        ;

        if ($commandVerbosity >= $verbosity) {
            $this->output
                ->write($messages)
            ;
        }
    }

    public function writeln($messages, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $commandVerbosity = $this->output
            ->getVerbosity()
        ;

        if ($commandVerbosity >= $verbosity) {
            $this->output
                ->writeln($messages)
            ;
        }
    }
}
