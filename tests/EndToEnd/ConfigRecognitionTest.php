<?php

namespace Crunz\Tests\EndToEnd;

use Crunz\Path\Path;
use Crunz\Tests\TestCase\EndToEndTestCase;

final class ConfigRecognitionTest extends EndToEndTestCase
{
    /**
     * @test
     * @TODO Remove in v2
     */
    public function configRecognitionRelatedToCrunzBinIsDeprecated()
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

        $process = $environment->runCrunzCommand('schedule:list', \sys_get_temp_dir());
        $normalizedOutput = $this->normalizeProcessOutput($process);

        $this->assertContains(
            '[Deprecation] Probably you are relying on legacy config file recognition which is deprecated.',
            $normalizedOutput
        );
        $this->assertContains(
            '[Deprecation] Probably you are relying on legacy tasks source recognition which is deprecated.',
            $normalizedOutput
        );
        $this->assertHasTask($normalizedOutput);
    }

    /** @test */
    public function searchConfigInCwd()
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

    /** @param string $output */
    private function assertHasTask($output)
    {
        $this->assertContains('PHP version', $output);
        $this->assertContains('php -v', $output);
    }
}
