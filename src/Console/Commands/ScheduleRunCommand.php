<?php

namespace Crunz\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Crunz\TaskfileFinder;

use Crunz\Schedule;
use Crunz\Invoker;

class ScheduleRunCommand extends Command
{
    /**
     * Command arguments
     *
     * @var array
     */
    protected $arguments;

    /**
     * Command options
     *
     * @var array
     */
    protected $options;

    /**
     * Default option values
     *
     * @var array
     */
    protected $defaults = [

        'src' => '/tasks',
    ];

    /**
     * Configures the current command
     *
     */
     protected function configure()
     {
        $this->setName('schedule:run')
             ->setDescription('Start the scheduler')
             ->setDefinition([
                new InputArgument('source', InputArgument::OPTIONAL, 'The source directory to collect the tasks.', getenv('CRUNZ_HOME') . $this->defaults['src']),
				new InputOption('task', '-t', InputOption::VALUE_OPTIONAL, 'The task to run from the list.', null), 
				new InputOption('force', '-f', InputOption::VALUE_NONE, 'The command will run instantly.', null), 
            ])
             ->setHelp('This command starts the scheduler.');
     } 
   
    /**
     * Executes the current command
     *
     * @param use Symfony\Component\Console\Input\InputInterface $input
     * @param use Symfony\Component\Console\Input\OutputIterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {        
        $this->arguments = $input->getArguments();
        $this->options   = $input->getOptions();
        $src             = $this->arguments['source'];
        $task_files      = TaskfileFinder::collectFiles($src); 
		$task			 = $this->options['task']; 
				    
        if (!count($task_files)) {
            $output->writeln('<comment>No task found!</comment>');
            exit();
        }
		
        $running_events = [];
        
        foreach ($task_files as $key => $taskFile) {
                        
            $schedule = require $taskFile->getRealPath();            
            if (!$schedule instanceof Schedule) {
                continue;
            } 

            $events = $schedule->dueEvents(new Invoker());   
		foreach ($events as $event) {
				
                echo '[', date('Y-m-d H:i:s'), '] Running scheduled command: ', $event->getSummaryForDisplay(), PHP_EOL;
                echo $event->buildCommand(), PHP_EOL;
                echo '---', PHP_EOL;
                
                // Running pre-execution hooks and the event itself
                $running_events[] = $event->callBeforeCallbacks(new Invoker())
                                          ->run(new Invoker());   
		}
        }
		
        if (!count($running_events)) {
            $output->writeln('<comment>No task is due!</comment>');
            exit();
        }

        // Running the post-execution hooks
        while (count($running_events)) {
            foreach ($running_events as $key => $event) {
                if ($event->process->isRunning()) {
                    continue;
                }

                $event->callAfterCallbacks(new Invoker());
                unset($running_events[$key]);
            }
        }
    }
  
}