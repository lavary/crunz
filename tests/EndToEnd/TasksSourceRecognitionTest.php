<?php

declare(strict_types=1);

namespace Crunz\Tests\EndToEnd;

use Crunz\Path\Path;
use Crunz\Tests\TestCase\EndToEndTestCase;

final class TasksSourceRecognitionTest extends EndToEndTestCase
{
    /**
     * @test
     * @TODO Remove in v2
     */
    public function tasksSourceRecognitionRelatedToCrunzBinIsDeprecated(): void
    {
        $envBuilder = $this->createEnvironmentBuilder();
        $envBuilder->addTask('PhpVersionTasks');

        $environment = $envBuilder->createEnvironment();

        $process = $environment->runCrunzCommand('schedule:list', \sys_get_temp_dir());
        $this->assertContains(
            '[Deprecation] Probably you are relying on legacy tasks source recognition which',
            $process->getOutput()
        );
        $this->assertHasTask($process->getOutput());
    }

    /** @test */
    public function searchTasksInCwd(): void
    {
        $envBuilder = $this->createEnvironmentBuilder();
        $envBuilder->addTask('PhpVersionTasks');

        $environment = $envBuilder->createEnvironment();

        $process = $environment->runCrunzCommand('schedule:list');

        $this->assertNotContains(
            '[Deprecation] Probably you are relying on legacy tasks source recognition which',
            $process->getOutput()
        );
        $this->assertHasTask($process->getOutput());
    }

    /** @test */
    public function searchTasksInCwdWithConfig(): void
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

        $this->assertNotContains(
            '[Deprecation] Probably you are relying on legacy tasks source recognition which',
            $process->getOutput()
        );
        $this->assertHasTask($process->getOutput());
    }

    /** @param string $output */
    private function assertHasTask($output): void
    {
        $this->assertContains('PHP version', $output);
        $this->assertContains('php -v', $output);
    }
}
