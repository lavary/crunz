<?php

use Crunz\Console\Commands\ScheduleRunCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ScheduleRunCommandTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		if (!file_exists(getenv('CRUNZ_HOME') . '/testtasks/'))
		{
			mkdir(getenv('CRUNZ_HOME') . '/testtasks/', 0744, true);
		}
		$stub = file_get_contents(getenv('CRUNZ_HOME') . '/src/Stubs/Test.php');
		file_put_contents(getenv('CRUNZ_HOME') . '/testtasks/TestTasks.php', $stub);
	}

	public function tearDown()
	{
		unlink(getenv('CRUNZ_HOME') . '/testtasks/TestTasks.php');
		if (file_exists(getenv('CRUNZ_HOME') . '/testtasks/'))
		{
			rmdir(getenv('CRUNZ_HOME') . '/testtasks/');
		}
	}
	
	public function testScheduleRunCommandOutputWithoutForceFlag()
	{
		$dw = (int) date( "w", time());
		$assertmsg = ($dw===0) ? "Test task" : 'No task is due';
		$this->executeCommand(false, false, $assertmsg);
	}

	public function testScheduleRunCommandOutputWithForceFlag()
	{
		$this->executeCommand(true, false, 'Test task');
	}

	public function testScheduleRunCommandOutputTaskDoesNotExist()
	{
		$this->executeCommand(false, 2, 'The requested task does not exists');
	}
	
	public function testScheduleRunCommandOutputTaskExist()
	{
		$dw = (int) date( "w", time());
		$assertmsg = ($dw===0) ? "Test task" : 'The requested task exists but it is not due.';
		$this->executeCommand(false, 1, $assertmsg);
	}

	protected function executeCommand($force, $task, $assertmsg)
	{
		$application = new Application();
		$application->add(new ScheduleRunCommand());
		$command = $application->find('schedule:run');
		$commandTester = new CommandTester($command);
		$array['command'] = $command->getName();
		$array['source'] = getenv('CRUNZ_HOME') . '/testtasks/';
		$array['-f'] = $force;
		
		if ($task)
		{
			$array['-t'] = (int) $task;
		}
		$commandTester->execute($array);

		$this->assertContains($assertmsg, $commandTester->getDisplay());
	}

}
