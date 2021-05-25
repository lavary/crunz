<?php

declare(strict_types=1);

namespace Crunz\Tests\EndToEnd;

use Crunz\Tests\TestCase\EndToEndTestCase;

final class LoggerTest extends EndToEndTestCase
{
    public function test_outputs_are_logged(): void
    {
        $envBuilder = $this->createEnvironmentBuilder();
        $envBuilder
            ->addTask('ClosureTasks')
            ->addTask('FailTasks')
            ->withConfig(
                [
                    'log_output' => true,
                    'output_log_file' => 'php://stdout',
                    'log_errors' => true,
                    'errors_log_file' => 'php://stderr',
                    'log_ignore_empty_context' => true,
                ]
            )
        ;
        $environment = $envBuilder->createEnvironment();
        $process = $environment->runCrunzCommand('schedule:run');

        $this->assertLogRecord(
            $process->getOutput(),
            'info',
            'Closure with output'
        );
        $this->assertLogRecord(
            $process->errorOutput(),
            'error',
            'Task that will fail'
        );
    }

    public function test_event_logging_override(): void
    {
        $envBuilder = $this->createEnvironmentBuilder()
            ->addTask('CustomOutputTasks')
            ->withConfig(
                [
                    'log_output' => true,
                    'output_log_file' => 'main.log',
                ]
            )
        ;
        $environment = $envBuilder->createEnvironment();
        $logPath = $environment->rootDirectory() . DIRECTORY_SEPARATOR;

        $process = $environment->runCrunzCommand('schedule:run');

        $this->assertEmpty($process->getOutput());

        $this->assertFileDoesNotExist("{$logPath}/main.log");

        $this->assertFileExists("{$logPath}/custom.log");
        $this->assertStringContainsString(
            'Usage: php',
            \file_get_contents("{$logPath}/custom.log")
        );
    }

    private function assertLogRecord(
        string $logRecord,
        string $level,
        string $message
    ): void {
        $levelFormatted = \mb_strtoupper($level);

        $this->assertMatchesRegularExpression(
            "/^\[[0-9]{4}(-[0-9]{2}){2} [0-9]{2}(:[0-9]{2}){2}\] crunz\.{$levelFormatted}:.+?({$message})/",
            $logRecord
        );
    }
}
