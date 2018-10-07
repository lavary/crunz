<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Configuration;

use Crunz\Configuration\ConfigFileNotExistsException;
use Crunz\Configuration\ConfigurationParser;
use Crunz\Configuration\Definition;
use Crunz\Configuration\FileParser;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Logger\ConsoleLoggerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationParserTest extends TestCase
{
    /** @test */
    public function useEmptyConfigWhenConfigFileNotExists()
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
    public function useParsedConfigWhenConfigFileExists()
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

    private function createConfigurationParser(FileParser $fileParser, array $expectedProcessedConfig)
    {
        $definition = new Definition();

        $definitionProcessorMock = $this->createMock(Processor::class);
        $definitionProcessorMock
            ->method('processConfiguration')
            ->with($definition, $expectedProcessedConfig);

        $filesystemMock = $this->createMock(FilesystemInterface::class);
        $filesystemMock
            ->method('fileExists')
            ->willReturn(true)
        ;

        return new ConfigurationParser(
            $definition,
            $definitionProcessorMock,
            $fileParser,
            $this->createMock(ConsoleLoggerInterface::class),
            $filesystemMock
        );
    }
}
