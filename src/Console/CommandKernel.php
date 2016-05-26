<?php

namespace Crunz\Console;

use Symfony\Component\Console\Application as SymfonyApplication;

class CommandKernel extends SymfonyApplication
{

    /**
     * List of commands to register
     *
     * @var array
     */
    protected $commands = [
        
        \Crunz\Console\Command\ScheduleRunCommand::class,
        \Crunz\Console\Command\ScheduleListCommand::class,
        \Crunz\Console\Command\TaskGeneratorCommand::class,
        \Crunz\Console\Command\ConfigGeneratorCommand::class,
    ];

    /**
     * Instantiate the class
     *
     */
    public function __construct($appName, $appVersion)
    {
        parent::__construct($appName, $appVersion);

        foreach($this->commands as $command) {
            $this->add(new $command);
        }
    }

    
    /**
     * Run the command
     *
     * @param array $arguments
     */
    public function handle()
    {    
        $this->run();
    }

}