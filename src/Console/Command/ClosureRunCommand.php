<?php

namespace Crunz\Console\Command;

use SuperClosure\Serializer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClosureRunCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('closure:run')
            ->setDescription('Executes a closure as a process.')
            ->setDefinition([
               new InputArgument('closure', InputArgument::REQUIRED, 'The closure to run'),
            ])
            ->setHelp('This command executes a closure as a separate process.');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $args = [];
        $this->arguments = $input->getArguments();

        parse_str($this->arguments['closure'], $args);
        $serializer = new Serializer();
        call_user_func_array($serializer->unserialize($args[0]), []);
    }
}
