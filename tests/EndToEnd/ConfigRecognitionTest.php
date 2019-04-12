<?php

declare(strict_types=1);

namespace Crunz\Tests\EndToEnd;

use Crunz\Path\Path;
use Crunz\Tests\TestCase\EndToEndTestCase;

final class ConfigRecognitionTest extends EndToEndTestCase
{
    /** @test */
    public function searchConfigInCwd(): void
    {
        $tasksSource = Path::fromStrings('resources', 'tasks');
        $environmentBuilder = $this->createEnvironmentBuilder();
        $environmentBuilder
            ->changeTaskDirectory($tasksSource)
            ->addTask('PhpVersionTasks')
            ->withConfig(
                [
                    'source' => $tasksSource->toString(),
                    'timezone' => 'UTC',
                ]
            )
        ;

        $environment = $environmentBuilder->createEnvironment();

        $process = $environment->runCrunzCommand('schedule:list');
        $normalizedOutput = $this->normalizeProcessOutput($process);

        $this->assertNotContains(
            '[Deprecation] Probably you are relying on legacy config file recognition which is deprecated.',
            $normalizedOutput
        );
        $this->assertNotContains(
            '[Deprecation] Probably you are relying on legacy tasks source recognition which is deprecated.',
            $normalizedOutput
        );
        $this->assertHasTask($normalizedOutput);
    }

    private function assertHasTask(string $output): void
    {
        $this->assertContains('PHP version', $output);
        $this->assertContains('php -v', $output);
    }
}
