<?php

declare(strict_types=1);

namespace Crunz\Tests\EndToEnd;

use Crunz\Console\Command\ConfigGeneratorCommand;
use Crunz\Filesystem\Filesystem;
use Crunz\Path\Path;
use Crunz\Tests\TestCase\EndToEndTestCase;
use Symfony\Component\Yaml\Yaml;

final class ConfigProviderTest extends EndToEndTestCase
{
    /** @test */
    public function configWillBePublishedToCwd(): void
    {
        $environmentBuilder = $this->createEnvironmentBuilder();
        $environment = $environmentBuilder->createEnvironment();
        $process = $environment->runCrunzCommand('publish:config');

        $configPath = Path::fromStrings($environment->rootDirectory(), ConfigGeneratorCommand::CONFIG_FILE_NAME);
        $filesystem = new Filesystem();

        $this->assertTrue($process->isSuccessful(), "Process output: {$process->getOutput()}{$process->errorOutput()}");
        $this->assertFileExists($configPath->toString());
        $this->assertIsArray(
            Yaml::parse(
                $filesystem->readContent(
                    $configPath->toString()
                )
            )
        );
    }
}
