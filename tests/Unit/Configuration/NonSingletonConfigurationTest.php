<?php

namespace Crunz\Tests\Unit\Configuration;

use Crunz\Configuration\FileParser;
use Crunz\Configuration\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class NonSingletonConfigurationTest extends TestCase
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
}
