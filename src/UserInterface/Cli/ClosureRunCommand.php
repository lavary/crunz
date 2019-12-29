<?php

declare(strict_types=1);

namespace Crunz\UserInterface\Cli;

use SuperClosure\Serializer;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClosureRunCommand extends SymfonyCommand
{
    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this
            ->setName('closure:run')
            ->setDescription('Executes a closure as a process.')
            ->setDefinition(
                [
                    new InputArgument(
                        'closure',
                        InputArgument::REQUIRED,
                        'The closure to run'
                    ),
                ]
            )
            ->setHelp('This command executes a closure as a separate process.')
            ->setHidden(true)
        ;
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $args = [];
        /** @var string $closure */
        $closure = $input->getArgument('closure');
        \parse_str($closure, $args);
        $serializer = new Serializer();

        \call_user_func_array($serializer->unserialize($args[0]), []);

        return 0;
    }
}
