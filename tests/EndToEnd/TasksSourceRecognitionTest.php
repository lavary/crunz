<?php

declare(strict_types=1);

namespace Crunz\Tests\EndToEnd;

use Crunz\Path\Path;
use Crunz\Tests\TestCase\EndToEndTestCase;

final class TasksSourceRecognitionTest extends EndToEndTestCase
{
    /** @test */
    public function search_tasks_in_cwd(): void
    {
        $envBuilder = $this->createEnvironmentBuilder();
        $envBuilder->addTask('PhpVersionTasks');

        $environment = $envBuilder->createEnvironment();

        $process = $environment->runCrunzCommand('schedule:list');

        $this->assertStringNotContainsString(
            '[Deprecation] Probably you are relying on legacy tasks source recognition which',
            $process->getOutput()
        );
        $this->assertHasTask($process->getOutput());
    }

    /** @test */
    public function search_tasks_in_cwd_with_config(): void
    {
        $tasksPath = Path::fromStrings('app', 'tasks');
        $envBuilder = $this->createEnvironmentBuilder();
        $envBuilder
            ->addTask('PhpVersionTasks')
            ->changeTaskDirectory($tasksPath)
            ->withConfig(['source' => $tasksPath->toString()])
        ;

        $environment = $envBuilder->createEnvironment();

        $process = $environment->runCrunzCommand('schedule:list');

        $this->assertStringNotContainsString(
            '[Deprecation] Probably you are relying on legacy tasks source recognition which',
            $process->getOutput()
        );
        $this->assertHasTask($process->getOutput());
    }

    private function assertHasTask(string $output): void
    {
        $this->assertStringContainsString('PHP version', $output);
        $this->assertStringContainsString('php -v', $output);
    }
}
