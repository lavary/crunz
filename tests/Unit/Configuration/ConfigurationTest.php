<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Configuration;

use Crunz\Configuration\Configuration;
use Crunz\Configuration\ConfigurationParserInterface;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Path\Path;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    /** @test */
    public function getCanReturnPathSplitByDot(): void
    {
        $configuration = $this->createConfiguration(
            [
                'smtp' => [
                    'port' => 1234,
                ],
            ]
        );

        $this->assertSame(1234, $configuration->get('smtp.port'));
    }

    /** @test */
    public function getReturnDefaultValueIfPathNotExists(): void
    {
        $configuration = $this->createConfiguration();

        $this->assertNull($configuration->get('wrong'));
        $this->assertSame('anon', $configuration->get('notExist', 'anon'));
    }

    /** @test */
    public function sourcePathIsRelativeToCwd(): void
    {
        $cwd = \sys_get_temp_dir();
        $sourcePath = Path::fromStrings('app', 'tasks');
        $expectedPath = Path::fromStrings($cwd, $sourcePath->toString());
        $configuration = $this->createConfiguration(['source' => $sourcePath->toString()], $cwd);

        $this->assertSame($expectedPath->toString(), $configuration->getSourcePath());
    }

    /** @test */
    public function sourcePathFallbackToTasksDirectory(): void
    {
        $cwd = \sys_get_temp_dir();
        $expectedPath = Path::fromStrings($cwd, 'tasks');
        $configuration = $this->createConfiguration([], $cwd);

        $this->assertSame($expectedPath->toString(), $configuration->getSourcePath());
    }

    /** @test */
    public function setConfigurationKeyValue(): void
    {
        $cwd = \sys_get_temp_dir();
        $sourcePath = Path::fromStrings('app', 'tasks');
        $configuration = $this->createConfiguration(['source' => $sourcePath->toString()], $cwd);

        $keyName = 'test_key';
        $expectedValue = 'test_value';

        $configuration->set($keyName, $expectedValue);

        $this->assertSame($configuration->get($keyName), $expectedValue);
    }

    /** @test */
    public function setConfigurationKeyArray(): void
    {
        $cwd = \sys_get_temp_dir();
        $sourcePath = Path::fromStrings('app', 'tasks');
        $configuration = $this->createConfiguration(['source' => $sourcePath->toString()], $cwd);

        $arrayName = 'test_array';
        $keyName = 'test_key';
        $expectedValue = 'test_value';

        $configuration->set("{$arrayName}.{$keyName}", $expectedValue);
        $expectedArray = $configuration->get($arrayName);

        $this->assertIsArray($expectedArray);
        $this->assertArrayHasKey($keyName, $expectedArray);
        $this->assertSame($expectedArray[$keyName], $expectedValue);
    }

    /** @param array<string,string|array> $config */
    private function createConfiguration(array $config = [], string $cwd = ''): Configuration
    {
        $mockConfigurationParser = $this->createMock(ConfigurationParserInterface::class);
        $mockConfigurationParser
            ->method('parseConfig')
            ->willReturn($config)
        ;

        $mockFilesystem = $this->createMock(FilesystemInterface::class);
        $mockFilesystem
            ->method('getCwd')
            ->willReturn($cwd)
        ;

        return new Configuration($mockConfigurationParser, $mockFilesystem);
    }
}
