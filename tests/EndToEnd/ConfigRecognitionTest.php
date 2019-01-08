<?php

namespace Crunz\Tests\EndToEnd;

use Crunz\Path\Path;
use Crunz\Tests\TestCase\EndToEndTestCase;

final class ConfigRecognitionTest extends EndToEndTestCase
{
    /** @var string */
    private $originalCwd;

    public function setUp()
    {
        $this->originalCwd = \getcwd();
    }

    protected function tearDown()
    {
        \chdir($this->originalCwd);
    }

    /**
     * @test
     * @TODO Remove in v2
     */
    public function configRecognitionRelatedToCrunzBinIsDeprecated()
    {
        \chdir('environments');

        $command = Path::create(
            [
                PHP_BINARY . ' config-recognition',
                'vendor',
                'bin',
                'crunz schedule:list',
            ]
        );

        $process = $this->createProcess($command->toString());
        $process->start();
        $process->wait();

        $this->assertContains(
            '[Deprecation] Probably you are relying on legacy config file recognition which is deprecated.',
            $process->getOutput()
        );
        $this->assertContains(
            '[Deprecation] Probably you are relying on legacy tasks source recognition which is deprecated.',
            $process->getOutput()
        );
        $this->assertHasTask($process->getOutput());
    }

    /** @test */
    public function searchConfigInCwd()
    {
        \chdir('environments/config-recognition');

        $command = Path::create(
            [
                PHP_BINARY . ' vendor',
                'bin',
                'crunz schedule:list',
            ]
        );

        $process = $this->createProcess($command->toString());
        $process->start();
        $process->wait();

        $this->assertNotContains(
            '[Deprecation] Probably you are relying on legacy config file recognition which is deprecated.',
            $process->getOutput()
        );
        $this->assertNotContains(
            '[Deprecation] Probably you are relying on legacy tasks source recognition which is deprecated.',
            $process->getOutput()
        );
        $this->assertHasTask($process->getOutput());
    }

    /** @param string $output */
    private function assertHasTask($output)
    {
        $this->assertContains('PHP version', $output);
        $this->assertContains('php -v', $output);
    }
}
