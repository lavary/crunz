<?php

declare(strict_types=1);

namespace Crunz\Tests\EndToEnd;

use Crunz\Tests\TestCase\EndToEndTestCase;

final class DebugTaskTest extends EndToEndTestCase
{
    public function test_task_debug(): void
    {
        $envBuilder = $this->createEnvironmentBuilder();
        $envBuilder
            ->addTask('ClosureTasks')
            ->withConfig(['timezone' => 'UTC'])
        ;

        $environment = $envBuilder->createEnvironment();

        $process = $environment->runCrunzCommand('task:debug 1');
        $output = $process->getOutput();
        $contentLines = $this->extractContentLines($output);

        $expectedValues = [
            'command_to_run' => 'Closure',
            'description' => 'Closure with output',
            'prevent_overlapping' => 'No',
            'cron_expression' => '* * * * *',
            'comparisons_timezone' => 'UTC (from config)',
        ];

        $this->assertHeader('debug_information_for_task_1', $contentLines);
        $this->assertHeader('example_run_dates', $contentLines);

        foreach ($expectedValues as $expectedKey => $expectedValue) {
            $this->assertArrayHasKey($expectedKey, $contentLines);
            $this->assertSame($expectedValue, $contentLines[$expectedKey]);
        }

        for ($i = 1; $i <= 5; ++$i) {
            $key = "_{$i}";
            $this->assertArrayHasKey($key, $contentLines);
            $this->assertRegExp('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:00 UTC$/', $contentLines[$key]);
        }
    }

    /** @return array<int,array<string,string>> */
    private function extractContentLines(string $output): array
    {
        $outputArray = \explode(PHP_EOL, $output);
        $contentLines = [];
        foreach ($outputArray as $line) {
            $matches = [];
            $match = \preg_match(
                "/(?<key>[ a-z0-9#']+) \|? (?<value>[ *\-:()a-z0-9#]+)/im",
                $line,
                $matches
            );

            if (1 !== $match) {
                continue;
            }

            $key = \trim($matches['key'] ?? '');
            $key = \mb_strtolower($key);
            $key = \str_replace(
                [
                    ' ',
                    '#',
                    "'",
                ],
                [
                    '_',
                    '_',
                    '',
                ],
                $key
            );

            $contentLines[$key] = \trim($matches['value'] ?? '');
        }

        return $contentLines;
    }

    private function assertHeader(string $header, array $lines): void
    {
        $this->assertArrayHasKey($header, $lines);
        $this->assertSame('', $lines[$header]);
    }
}
