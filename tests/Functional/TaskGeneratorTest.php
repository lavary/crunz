<?php

declare(strict_types=1);

namespace Crunz\Tests\Functional;

use Crunz\Application;
use Crunz\Path\Path;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class TaskGeneratorTest extends TestCase
{
    /** @var string */
    private $fileName;
    /** @var string */
    private $taskFilePath;
    /** @var string */
    private $outputDirectory;

    public function setUp()
    {
        $this->outputDirectory = \sys_get_temp_dir();
        $this->fileName = 'CrunzTest';
        $taskFilePath = Path::create(
            [
                $this->outputDirectory,
                "{$this->fileName}Tasks.php",
            ]
        );
        $this->taskFilePath = $taskFilePath->toString();
        $this->clearTask();
    }

    public function tearDown()
    {
        $this->clearTask();
    }

    /** @test */
    public function generateTaskFile()
    {
        $application = new Application('Crunz', '0.1.0-test.1');
        $command = $application->get('make:task');

        $commandTester = new CommandTester($command);
        $this->provideAnswer(
            "{$this->outputDirectory}\n",
            $commandTester,
            $command
        );
        $returnCode = $commandTester->execute(
            [
                'taskfile' => $this->fileName,
            ]
        );

        $this->assertSame(0, $returnCode);
        $this->assertFileExists($this->taskFilePath);
    }

    private function getInputStream($input)
    {
        $stream = \fopen('php://memory', 'rb+', false);
        \fwrite($stream, $input);
        \rewind($stream);

        return $stream;
    }

    private function clearTask()
    {
        if (\file_exists($this->taskFilePath)) {
            \unlink($this->taskFilePath);
        }
    }

    private function provideAnswer(
        $answer,
        CommandTester $commandTester,
        Command $command
    ) {
        if (\method_exists($commandTester, 'setInputs')) {
            $commandTester->setInputs([$answer]);

            return;
        }

        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream($answer));
    }
}
