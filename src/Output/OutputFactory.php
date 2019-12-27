<?php

declare(strict_types=1);

namespace Crunz\Output;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class OutputFactory
{
    /** @var InputInterface */
    private $input;

    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    public function createOutput(): OutputInterface
    {
        $input = $this->input;
        $output = new ConsoleOutput();

        if (true === $input->hasParameterOption(['--quiet', '-q'])) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        } elseif ($input->hasParameterOption('-vvv') || $input->hasParameterOption('--verbose=3') || 3 === $input->getParameterOption('--verbose')) {
            $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        } elseif ($input->hasParameterOption('-vv') || $input->hasParameterOption('--verbose=2') || 2 === $input->getParameterOption('--verbose')) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        } elseif ($input->hasParameterOption('-v') || $input->hasParameterOption('--verbose=1') || $input->hasParameterOption('--verbose') || $input->getParameterOption('--verbose')) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        }

        return $output;
    }
}
