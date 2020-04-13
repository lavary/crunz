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

    private function assertLogRecord(
        string $logRecord,
        string $level,
        string $message
    ): void {
        $levelFormatted = \mb_strtoupper($level);

        $this->assertRegExp(
            "/^\[[0-9]{4}(-[0-9]{2}){2} [0-9]{2}(:[0-9]{2}){2}\] crunz\.{$levelFormatted}:.+?({$message})/",
            $logRecord
        );
    }
}
