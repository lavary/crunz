<?php

declare(strict_types=1);

namespace Crunz\Tests\EndToEnd;

use Crunz\Path\Path;
use Crunz\Tests\TestCase\EndToEndTestCase;

final class ConfigRecognitionTest extends EndToEndTestCase
{
    /** @var string */
    private $originalCwd;

    public function setUp(): void
    {
        $this->originalCwd = \getcwd();
    }

    protected function tearDown(): void
    {
        \chdir($this->originalCwd);
    }

    /**
     * @test
     * @TODO Remove in v2
     */
    public function configRecognitionRelatedToCrunzBinIsDeprecated(): void
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
        $normalizedOutput = \preg_replace(
            "/\s+/",
            ' ',
            $process->getOutput()
        );

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
        $normalizedOutput = \preg_replace(
            "/\s+/",
            ' ',
            $process->getOutput()
        );

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
    private function assertHasTask($output): void
    {
        $this->assertContains('PHP version', $output);
        $this->assertContains('php -v', $output);
    }
}
