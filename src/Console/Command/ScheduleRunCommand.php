<?php

namespace Crunz\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Crunz\Schedule;
use Crunz\Invoker;
use Crunz\EventRunner;
use Crunz\Configuration\Configurable;

class ScheduleRunCommand extends Command
{
    use Configurable;

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
       $this->configurable();
       
       $this->setName('schedule:run')
            ->setDescription('Starts the event runner.')
            ->setDefinition([
               new InputArgument('source', InputArgument::OPTIONAL, 'The source directory for collecting the task files.', generate_path($this->config('source'))), 
           ])
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
        $files           = $this->collectFiles($this->arguments['source']); 
    
        if (!count($files)) {
            $output->writeln('<comment>No task found! Please check your source path.</comment>');
            exit();
        }
                       
        // List of schedules
        $schedules = [];
        
        foreach ($files as $file) {
            
            $schedule = require $file->getRealPath();           
            if (!$schedule instanceof Schedule) {
                continue;
            }

            // We keep the events which are due and dismiss the rest.
            $schedule->events($schedule->dueEvents());            
            
            if (count($schedule->events())) {
                $schedules[] = $schedule;
            }
        }

        if (!count($schedules)) {
            $output->writeln('<comment>No event is due!</comment>');
            exit();
        }

        // Running the events
        (new EventRunner())
        ->handle($schedules);
    }

    /**
     * Collect all task files
     *
     * @param  string $source
     *
     * @return Iterator
     */
    protected function collectFiles($source)
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