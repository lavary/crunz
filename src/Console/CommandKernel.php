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
        
        \Crunz\console\Commands\ScheduleRunCommand::class,
        \Crunz\console\Commands\ScheduleListCommand::class,
        \Crunz\Console\Commands\TaskGeneratorCommand::class,
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