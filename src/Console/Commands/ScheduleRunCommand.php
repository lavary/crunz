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
	 * Console Output
	 *
	 * @var Symfony\Component\Console\Input\OutputIterface $output
	 */
	protected $output;

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
		$this->options = $input->getOptions();
		$src = $this->arguments['source'];
		$task_files = TaskfileFinder::collectFiles($src);
		$requested_task_number = $this->options['task'];
		$force = (bool) $this->options['force'];
		$this->output = $output;

		$events = $this->getEvents($task_files, $requested_task_number, $force);
		$this->runEvents($events);
	}

	/**
	 * Gets all the events
	 *
	 * @param $task_files
	 * @param $requested_task_number If this is set the method only returns the requested task
	 * @param $force If set to true, this method returns all events, if false it only returns events which are due
	 *
	 * @return array Returns all the events as requested
	 */
	protected function getEvents($task_files, $requested_task_number, $force)
	{
		if (!count($task_files))
		{
			$this->outputComment('No task found!');
		}

		foreach ($task_files as $taskFile) {

			$schedule = require $taskFile->getRealPath();
			if (!$schedule instanceof Schedule)
			{
				continue;
			}
			$eventsTemp = $schedule->events();
			foreach ($eventsTemp as $event) {
				$events[] = $event;
			}
			$dueEventsTemp = $schedule->dueEvents(new Invoker());
			foreach ($dueEventsTemp as $dueEvent) {
				$dueEvents[] = $dueEvent;
			}
		}
		
		if ($requested_task_number !== null)
		{
			return $this->getTask($events, $dueEvents, $requested_task_number, $force);
		}
		else
		{
			return ($force) ? $events : $dueEvents;
		}
	}

	/**
	 * Runs all the events
	 *
	 * @param $events
	 * @return void
	 */
	protected function runEvents($events)
	{
		$running_events = [];
		foreach ($events as $event) {

			echo '[', date('Y-m-d H:i:s'), '] Running scheduled command: ', $event->getSummaryForDisplay(), PHP_EOL;
			echo $event->buildCommand(), PHP_EOL;
			echo '---', PHP_EOL;

			// Running pre-execution hooks and the event itself
			$running_events[] = $event->callBeforeCallbacks(new Invoker())
					->run(new Invoker());
		}

		if (!count($running_events))
		{
			$this->outputComment('No task is due!');
		}

		// Running the post-execution hooks
		while (count($running_events)) {
			foreach ($running_events as $key => $event) {
				if ($event->process->isRunning())
				{
					continue;
				}

				$event->callAfterCallbacks(new Invoker());
				unset($running_events[$key]);
			}
		}
	}

	/**
	 * Gets the requested task
	 *
	 * @param $events  All available events
	 * @param $dueEvents  All due events
	 * @param $requested_task_number The requested task, as it is defined using schedule:list
	 *
	 * @return array Returns the requested event, exit with error message otherwise
	 */
	protected function getTask($events, $dueEvents, $requested_task_number, $force)
	{
		// The schdeule:list command displays the tasks starting with 1, and the events array is 0 based, 
		// so we need to substract 1 from the requested task number  
		$requested_task_key = $requested_task_number - 1;
		
		if (empty($events[$requested_task_key]))
		{
			$this->outputComment('The requested task does not exists.');
		}
		elseif (!empty($events[$requested_task_key]) && empty($dueEvents[$requested_task_key]) && !$force)
		{
			$this->outputComment('The requested task exists but it is not due.');
		}
		else
		{
			return [$events[$requested_task_key]];
		}
	}

	protected function outputComment($comment = '')
	{
		if (isset($this->output))
		{
			$this->output->writeln("<comment>$comment</comment>");
		}
		exit();
	}

}
