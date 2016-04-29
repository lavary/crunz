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
		$requsted_task = $this->options['task'];
		$force = (bool) $this->options['force'];
		$this->output = $output;

		$events = $this->getEvents($task_files, $requsted_task, $force);
		$this->runEvents($events);
	}

	/**
	 * Gets all the events
	 *
	 * @param $task_files
	 * @param $requsted_task If this is set the method only return the requested task
	 * @param $force If true, this method returns all events otherwise it only returns due events
	 *
	 * @return array Returns all the events as requested
	 */
	protected function getEvents($task_files, $requsted_task, $force)
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

			$events = $schedule->events();
			$dueEvents = $schedule->dueEvents(new Invoker());
		}
		if ($requsted_task !== null)
		{
			return $this->getTask($events, $dueEvents, $requsted_task, $force);
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
	 * @param $requested_task The number of requested task, as it is listed using schedule:list
	 *
	 * @return array Returns the requested event, exit with error message otherwise
	 */
	protected function getTask($events, $dueEvents, $requsted_task, $force)
	{
		if (empty($events[$requsted_task - 1]))
		{
			$this->outputComment('The requested task does not exists.');
		}
		elseif (!empty($events[$requsted_task - 1]) && empty($dueEvents[$requsted_task - 1]) && !$force)
		{
			$this->outputComment('The requested task exists but it is not due.');
		}
		else
		{
			return [$events[$requsted_task - 1]];
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
