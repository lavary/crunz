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
        
        // This command starts the event runner (vendor/bin/crunz schedule:run)
        // It takes an optional argument which is the source directory for tasks
        // If the argument is not provided, the default in the configuratrion file
        // will be considered as the source path
        \Crunz\Console\Command\ScheduleRunCommand::class,
        
        
        // This command (vendor/bin/schedule:list) lists the scheduled events in different task files
        // Just like schedule:run it gets the :source argument
        \Crunz\Console\Command\ScheduleListCommand::class,
        
        
        // This command generates a task from the command-line
        // This is often useful when you want to create a task file and start
        // adding tasks to it.
        \Crunz\Console\Command\TaskGeneratorCommand::class,
        
        
        // The modify the configuration, the user's own copy should be modified
        // This command creates a configuration file in Crunz installation directory
        \Crunz\Console\Command\ConfigGeneratorCommand::class,
        
        // This command is used by Crunz itself for running serialized closures
        // It accepts an argument which is the serialized form of the closure to run.
        \Crunz\Console\Command\ClosureRunCommand::class,
        
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