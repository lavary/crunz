<?php

namespace Crunz\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Finder\Finder;

use Crunz\Schedule;
use Crunz\Configuration;

class ScheduleListCommand extends Command
{
    /**
     * Configures the current command
     *
     */
    protected function configure()
    {
       $this->setName('schedule:list')
            ->setDescription('Display the list of scheduled tasks.')
            ->setDefinition([
               new InputArgument('source', InputArgument::OPTIONAL, 'The source directory to collect the tasks.', $this->config('tasks_path')), 
           ])
           ->setConfiguration(Configuration::getInstance())
           ->setHelp('This command displays the scheduled tasks in a tabular format.');
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
        $this->options   = $input->getOptions();
        $this->arguments = $input->getArguments();
        $src             = !is_null($this->arguments['source']) ? $this->arguments['source'] : $this->config('source');
        
        $task_files      = $this->collectTaskFiles($src); 
    
        if (!count($task_files)) {
            $output->writeln('<comment>No task found!</comment>');
            exit();
        }

        $table = new Table($output);   
        $table->setHeaders(['#', 'Task', 'Expression', 'Command to Run']);
        $row = 0;
        
        foreach ($task_files as $key => $taskFile) {
                        
            $schedule = require $taskFile->getRealPath();            
            if (!$schedule instanceof Schedule) {
                continue;
            } 
            
            $events = $schedule->events();
            foreach ($events as $event) {
              
              $table->addRow([
                ++$row,
                $event->description,
                $event->getExpression(),
                $event->command,
              ]); 

            }
        }

        $table->render(); 
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