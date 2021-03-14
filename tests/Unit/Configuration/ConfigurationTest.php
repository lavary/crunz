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
    public function get_can_return_path_split_by_dot(): void
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
    public function get_return_default_value_if_path_not_exists(): void
    {
        $configuration = $this->createConfiguration();

        $this->assertNull($configuration->get('wrong'));
        $this->assertSame('anon', $configuration->get('notExist', 'anon'));
    }

    /** @test */
    public function source_path_is_relative_to_cwd(): void
    {
        $cwd = \sys_get_temp_dir();
        $sourcePath = Path::fromStrings('app', 'tasks');
        $expectedPath = Path::fromStrings($cwd, $sourcePath->toString());
        $configuration = $this->createConfiguration(['source' => $sourcePath->toString()], $cwd);

        $this->assertSame($expectedPath->toString(), $configuration->getSourcePath());
    }

    /** @test */
    public function source_path_fallback_to_tasks_directory(): void
    {
        $cwd = \sys_get_temp_dir();
        $expectedPath = Path::fromStrings($cwd, 'tasks');
        $configuration = $this->createConfiguration([], $cwd);

        $this->assertSame($expectedPath->toString(), $configuration->getSourcePath());
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
