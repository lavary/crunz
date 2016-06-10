<?php

namespace Crunz\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;

use Crunz\Schedule;
use Crunz\Invoker;
use Crunz\Configuration;

class ScheduleRunCommand extends Command
{
    /**
     * Running tasks
     *
     * @var array
     */
    protected $runningEvents = [];

    /**
     * Configures the current command
     *
     */
    protected function configure()
    {
       $this->setName('schedule:run')
            ->setDescription('Start the event runner.')
            ->setDefinition([
               new InputArgument('source', InputArgument::OPTIONAL, 'The source directory to collect the tasks.', $this->config('tasks_path')), 
           ])
           ->setConfiguration(Configuration::getInstance())
           ->setHelp('This command starts the Crunz event runner.');
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
        
        $src             = !is_null($this->arguments['source']) ? $this->arguments['source'] : $this->config('source');
        $task_files      = $this->collectTaskFiles($src); 
    
        if (!count($task_files)) {
            $output->writeln('<comment>No task found!</comment>');
            exit();
        }
        
        $this->runTasks($task_files);

        if (!count($this->runningEvents)) {
            $output->writeln('<comment>No task is due!</comment>');
            exit();
        }

        // Managing the running tasks
        while (count($this->runningEvents)) {
            
            foreach ($this->runningEvents as $key => $event) {
                
                // If the process is still running then skip
                if ($event->process->isRunning()) {
                    continue;
                }
                                
                $log = date('Y-m-d H:i:s') . ' [Performed: ' . $event->getSummaryForDisplay() . '] by running ' . $event->buildCommand() . PHP_EOL;                
                
                if ($event->process->isSuccessful()) {
                    
                    // Running post-execution hooks
                    $event->callAfterCallbacks(new Invoker());
                    
                    // Logging the output based on the defined configuration
                    $output = $log . PHP_EOL . $event->process->getOutput();          
                    
                    if ($this->config('log_output')) {                          
                        $event->logOutput($output, $this->config('output_log_file'), true);
                    } else if ($event->output != '/dev/null') {
                        $event->logEventOutput($output);  
                    }
                    
                // Logging the errors if log_error is set in the configuration file
                } else {
                    
                    if ($this->config('log_errors')) {
                        $err_msg = date('Y-m-d H:i:s') . '  [Error in ' . $event->getSummaryForDisplay() . ']: "' . $event->process->getErrorOutput() . '" while running ' . $event->buildCommand() . PHP_EOL;                    
                        error_log($err_msg, 3, $this->config('errors_log_file'));
                    }

                }
                
                echo $log;
                unset($this->runningEvents[$key]);
            }
        }
    }
 
    /**
     * Run the tasks
     *
     * @param  array $task_files
     */
    public function runTasks($task_files = [])
    {
        foreach ($task_files as $key => $taskFile) {
                        
            $schedule = require $taskFile->getRealPath();            
            if (!$schedule instanceof Schedule) {
                continue;
            } 

            $events = $schedule->dueEvents(new Invoker());                        
            
            foreach ($events as $event) {
                
                // Running pre-execution hooks and the event itself
                $this->runningEvents[] = $event->callBeforeCallbacks(new Invoker())
                                          ->run(new Invoker());                
            }
        }
    }

    /**
     * Return all the running tasks
     *
     * @return Array
     */
    public function runningTasks()
    {
        return $this->runningTasks;
    }

    /**
     * Collect all task files
     *
     * @param  string $source
     * @return Iterator
     */
    public function collectTaskFiles($source)
    {    
        if(!file_exists($source)) {
            return [];
        }
        
        $finder   = new Finder();
        $iterator = $finder->files()
                  ->name('*' . $this->config('suffix'))
                  ->in($source);
        
        return $iterator;
    }
  
}