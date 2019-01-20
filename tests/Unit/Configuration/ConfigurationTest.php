<?php

namespace Crunz\Tests\Unit\Configuration;

use Crunz\Configuration\Configuration;
use Crunz\Configuration\ConfigurationParserInterface;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Path\Path;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    /** @test */
    public function getCanReturnPathSplitByDot()
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
    public function getReturnDefaultValueIfPathNotExists()
    {
        $configuration = $this->createConfiguration();

        $this->assertNull($configuration->get('wrong'));
        $this->assertSame('anon', $configuration->get('notExist', 'anon'));
    }

    /** @test */
    public function sourcePathIsRelativeToCwd()
    {
        $cwd = \sys_get_temp_dir();
        $sourcePath = Path::fromStrings('app', 'tasks');
        $expectedPath = Path::fromStrings($cwd, $sourcePath->toString());
        $configuration = $this->createConfiguration(['source' => $sourcePath->toString()], $cwd);

        $this->assertSame($expectedPath->toString(), $configuration->getSourcePath());
    }

    /** @test */
    public function sourcePathFallbackToTasksDirectory()
    {
        $cwd = \sys_get_temp_dir();
        $expectedPath = Path::fromStrings($cwd, 'tasks');
        $configuration = $this->createConfiguration([], $cwd);

        $this->assertSame($expectedPath->toString(), $configuration->getSourcePath());
    }

    /**
     * @test
     *
     * @TODO Remove in v2
     */
    public function legacySourcePathsAreRelativeToCrunzBin()
    {
        \defined('CRUNZ_BIN_DIR') ?: \define('CRUNZ_BIN_DIR', \sys_get_temp_dir());
        $crunzBinDir = CRUNZ_BIN_DIR;
        $expectedPaths = [
            Path::create(
                [
                    $crunzBinDir,
                    '..',
                    '..',
                    'tasks',
                ]
            )->toString(),
            Path::create(
                [
                    $crunzBinDir,
                    '..',
                    '..',
                    '..',
                    'tasks',
                ]
            )->toString(),
        ];

        $configuration = $this->createConfiguration(['source' => 'tasks']);

        $paths = $configuration->binRelativeSourcePaths();

        $this->assertSame($expectedPaths, $paths);
    }

    /** @return Configuration */
    private function createConfiguration(array $config = [], $cwd = '')
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
