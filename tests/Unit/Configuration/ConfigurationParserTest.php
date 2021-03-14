<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Configuration;

use Crunz\Configuration\ConfigFileNotExistsException;
use Crunz\Configuration\ConfigurationParser;
use Crunz\Configuration\Definition;
use Crunz\Configuration\FileParser;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Tests\TestCase\Logger\NullLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationParserTest extends TestCase
{
    /** @test */
    public function use_empty_config_when_config_file_not_exists(): void
    {
        $this->addToAssertionCount(1);

        $fileParserMock = $this->createMock(FileParser::class);
        $fileParserMock
            ->method('parse')
            ->willThrowException(ConfigFileNotExistsException::fromFilePath('/path'))
        ;

        $configurationParser = $this->createConfigurationParser($fileParserMock, []);
        $configurationParser->parseConfig();
    }

    /** @test */
    public function use_parsed_config_when_config_file_exists(): void
    {
        $this->addToAssertionCount(1);

        $parsedConfig = ['some' => 'config'];

        $fileParserMock = $this->createMock(FileParser::class);
        $fileParserMock
            ->method('parse')
            ->willReturn($parsedConfig)
        ;

        $configurationParser = $this->createConfigurationParser($fileParserMock, $parsedConfig);
        $configurationParser->parseConfig();
    }

    /** @param array<string,string> $expectedProcessedConfig */
    private function createConfigurationParser(
        FileParser $fileParser,
        array $expectedProcessedConfig
    ): ConfigurationParser {
        $definition = new Definition();

        $definitionProcessorMock = $this->createMock(Processor::class);
        $definitionProcessorMock
            ->method('processConfiguration')
            ->with($definition, $expectedProcessedConfig)
            ->willReturn([])
        ;

        $filesystemMock = $this->createMock(FilesystemInterface::class);
        $filesystemMock
            ->method('fileExists')
            ->willReturn(true)
        ;

        return new ConfigurationParser(
            $definition,
            $definitionProcessorMock,
            $fileParser,
            new NullLogger(),
            $filesystemMock
        );
    }
}
