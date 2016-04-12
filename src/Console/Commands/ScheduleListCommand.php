<?php

namespace Crunz\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Finder\Finder;

use Crunz\Schedule;

class ScheduleListCommand extends Command
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
        $this->setName('schedule:list')
             ->setDescription('Display the list of scheduled tasks')
             ->setDefinition([
                new InputArgument('source', InputArgument::OPTIONAL, 'The source directory to collect the tasks.', getenv('CRUNZ_HOME') . $this->defaults['src']), 
            ])
             ->setHelp('This command displays the scheduled tasks in tabular format.');
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
        $src             = array_get($this->arguments, 'source');
        
        $task_files      = $this->collectFiles($src); 
    
        if (!count($task_files)) {
            $output->writeln('<comment>No task found!</comment>');
            exit();
        }

        $table = new Table($output);   
        $table->setHeaders(['#', 'Task', 'Expression', 'Command to Run']);

        foreach ($task_files as $key => $taskFile) {
                        
            $schedule = require $taskFile->getRealPath();            
            if (!$schedule instanceof Schedule) {
                continue;
            } 
            
            foreach ($schedule->events() as $key => $event) {
              
              $table->addRow([
                $key + 1,
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
    public static function collectFiles($source)
    {    
        if(!file_exists($source)) {
            return [];
        }
        
        $finder   = new Finder();
        $iterator = $finder->files()
                  ->name('*Tasks.php')
                  ->in($source);
        
        return $iterator;
    }
  
}