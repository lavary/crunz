<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Configuration;

use Crunz\Configuration\ConfigFileNotExistsException;
use Crunz\Configuration\ConfigFileNotReadableException;
use Crunz\Configuration\FileParser;
use Crunz\Tests\TestCase\TemporaryFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class FileParserTest extends TestCase
{
    /** @test */
    public function parseThrowsExceptionOnNonExistingFile(): void
    {
        $filePath = '/path/to/wrong/file';

        $this->expectException(ConfigFileNotExistsException::class);
        $this->expectExceptionMessage("Configuration file '{$filePath}' not exists.");

        $parser = $this->createFileParser();
        $parser->parse($filePath);
    }

    /** @test */
    public function parseThrowsExceptionOnNonReadableFile(): void
    {
        if ($this->isWindows()) {
            $this->markTestSkipped('Required Unix-based OS.');
        }

        $tempFile = new TemporaryFile();
        $tempFile->changePermissions(0200);
        $filePath = $tempFile->filePath();

        $this->expectException(ConfigFileNotReadableException::class);
        $this->expectExceptionMessage("Config file '{$filePath}' is not readable.");

        $parser = $this->createFileParser();
        $parser->parse($filePath);
    }

    /** @test */
    public function parseReturnsParsedFileContent(): void
    {
        $tempFile = new TemporaryFile();
        $filePath = $tempFile->filePath();
        $configData = [
            'suffix' => 'Task.php',
            'source' => 'tasks',
        ];
        \file_put_contents($filePath, Yaml::dump($configData));

        $parser = $this->createFileParser();

        $this->assertSame([$configData], $parser->parse($filePath));
    }

    /**
     * @return FileParser
     */
    private function createFileParser()
    {
        return new FileParser(new Yaml());
    }

    /**
     * @return bool
     */
    private function isWindows()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }
}
