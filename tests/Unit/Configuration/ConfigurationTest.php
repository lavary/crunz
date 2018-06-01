<?php

namespace Crunz\Tests\Unit\Configuration;

use Crunz\Configuration\Configuration;
use Crunz\Configuration\FileParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ConfigurationTest extends TestCase
{
    /** @test */
    public function getCanReturnPathSplitByDot()
    {
        $configuration = $this->createConfiguration();

        $this->assertSame(1234, $configuration->get('smtp.port'));
    }

    /** @test */
    public function getReturnDefaultValueIfPathNotExists()
    {
        $configuration = $this->createConfiguration();

        $this->assertSame(null, $configuration->get('wrong'));
        $this->assertSame('anon', $configuration->get('notExist', 'anon'));
    }

    /** @test */
    public function checkConfigInProjectRootFirst()
    {
        $expectedPath = $this->makePath([getbase(), 'crunz.yml']);

        $this->createConfigurationForPathChecks($expectedPath);
        $this->assertTrue(true);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function checkConfigInCwdAfterProjectRoot()
    {
        setbase(\sys_get_temp_dir());

        $expectedPath = $this->makePath([\getcwd(), 'crunz.yml']);

        $this->createConfigurationForPathChecks($expectedPath);
        $this->assertTrue(true);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function fallbackConfigPathToCrunz()
    {
        setbase(\sys_get_temp_dir());
        \chdir(\sys_get_temp_dir());

        $expectedPath = $this->makePath([CRUNZ_ROOT, 'crunz.yml']);

        $this->createConfigurationForPathChecks($expectedPath);
        $this->assertTrue(true);
    }

    private function createConfigurationForPathChecks($expectedPath)
    {
        $mockDefinitionProcessor = $this->createMock(Processor::class);
        $mockFileParser = $this->createMock(FileParser::class);
        $mockConfigurationDefinition = $this->createMock(ConfigurationInterface::class);

        $mockFileParser
            ->method('parse')
            ->with($expectedPath)
            ->willReturn([])
        ;

        return new Configuration(
            $mockConfigurationDefinition,
            $mockDefinitionProcessor,
            $mockFileParser,
            new PropertyAccessor(false, true)
        );
    }

    private function createConfiguration()
    {
        $mockDefinitionProcessor = $this->createMock(Processor::class);
        $mockFileParser = $this->createMock(FileParser::class);
        $mockConfigurationDefinition = $this->createMock(ConfigurationInterface::class);

        $mockDefinitionProcessor
            ->method('processConfiguration')
            ->willReturn(
                [
                    'smtp' => [
                        'port' => 1234,
                    ],
                ]
            )
        ;
        $mockFileParser
            ->method('parse')
            ->willReturn([])
        ;

        return new Configuration(
            $mockConfigurationDefinition,
            $mockDefinitionProcessor,
            $mockFileParser,
            new PropertyAccessor(false, true)
        );
    }

    private function makePath($parts)
    {
        return \implode(DIRECTORY_SEPARATOR, $parts);
    }
}
