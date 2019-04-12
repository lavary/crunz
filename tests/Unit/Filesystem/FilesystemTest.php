<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Filesystem;

use Crunz\Filesystem\Filesystem;
use Crunz\Path\Path;
use Crunz\Tests\TestCase\TemporaryFile;
use PHPUnit\Framework\TestCase;

final class FilesystemTest extends TestCase
{
    /** @test */
    public function cwdIsCorrect(): void
    {
        $filesystem = new Filesystem();

        $this->assertSame(\getcwd(), $filesystem->getCwd());
    }

    /**
     * @dataProvider fileExistsProvider
     * @test
     */
    public function fileExistsIsCorrect($path, $expectedExistence): void
    {
        $filesystem = new Filesystem();

        $this->assertSame($expectedExistence, $filesystem->fileExists($path));
    }

    /** @test */
    public function tempDirectoryReturnSystemTempDirectory(): void
    {
        $filesystem = new Filesystem();

        $this->assertSame(\sys_get_temp_dir(), $filesystem->tempDir());
    }

    /** @test */
    public function removeDirectoryRemovesDirectoriesRecursively(): void
    {
        $filesystem = new Filesystem();

        $tempDir = \sys_get_temp_dir();
        $rootPath = Path::fromStrings($tempDir, 'fs-tests');
        $innerPath = Path::fromStrings($rootPath->toString(), 'inner');
        $filePath = Path::fromStrings($innerPath->toString(), 'some-file.txt');

        \mkdir(
            $innerPath->toString(),
            0777,
            true
        );
        \touch($filePath->toString());

        $filesystem->removeDirectory($rootPath->toString());

        $this->assertDirectoryNotExists($rootPath->toString());
    }

    /** @test */
    public function dumpFileWritesContentToFile(): void
    {
        $content = 'Some content';
        $tempDir = \sys_get_temp_dir();
        $filePath = Path::fromStrings($tempDir, 'dump-file.txt');

        $filesystem = new Filesystem();
        $filesystem->dumpFile($filePath->toString(), $content);

        $this->assertStringEqualsFile($filePath->toString(), $content);

        \unlink($filePath->toString());
    }

    /** @test */
    public function createDirectoryCreatesDirectoryRecursive(): void
    {
        $tempDir = \sys_get_temp_dir();
        $rootDirectoryPath = Path::fromStrings($tempDir, 'crunz-test');
        $directoryPath = Path::fromStrings(
            $rootDirectoryPath->toString(),
            'deep',
            'path',
            'here'
        );

        $filesystem = new Filesystem();
        $filesystem->createDirectory($directoryPath->toString());

        $this->assertDirectoryExists($directoryPath->toString());

        $filesystem->removeDirectory($rootDirectoryPath->toString());
    }

    /** @test */
    public function copyFiles(): void
    {
        $content = 'Copy content';
        $tempDir = \sys_get_temp_dir();
        $rootDirectoryPath = Path::fromStrings($tempDir, 'copy-test');
        \mkdir($rootDirectoryPath->toString());
        $filePath = Path::fromStrings($rootDirectoryPath->toString(), 'file1.txt');
        $targetFile = Path::fromStrings($rootDirectoryPath->toString(), 'file-copy.txt');
        \file_put_contents($filePath->toString(), $content);

        $filesystem = new Filesystem();
        $filesystem->copy($filePath->toString(), $targetFile->toString());

        $this->assertFileExists($targetFile->toString());
        $this->assertStringEqualsFile($targetFile->toString(), $content);

        $filesystem->removeDirectory($rootDirectoryPath->toString());
    }

    /** @test */
    public function projectRootDirectory(): void
    {
        $filesystem = new Filesystem();

        $this->assertSame($this->findProjectRootDirectory(), $filesystem->projectRootDirectory());
    }

    /** @test */
    public function readContentReturnFileContent(): void
    {
        $filesystem = new Filesystem();
        $content = $filesystem->readContent(__FILE__);

        $this->assertContains('final class FilesystemTest extends TestCase', $content);
    }

    /** @test */
    public function readContentThrowsExceptionWhenFileNotExists(): void
    {
        $path = Path::fromStrings(\sys_get_temp_dir(), 'wrong-file');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("File '{$path->toString()}' doesn't exists.");

        $filesystem = new Filesystem();
        $filesystem->readContent($path->toString());
    }

    public function fileExistsProvider()
    {
        $tempFile = new TemporaryFile();

        yield 'exists' => [
            $tempFile->filePath(),
            true,
            // Param used to avoid GC
            $tempFile,
        ];

        yield 'notExists' => [
            '/some/wrong/path',
            false,
        ];
    }

    private function findProjectRootDirectory()
    {
        $dir = $rootDir = \dirname(__DIR__);
        $path = Path::fromStrings($dir, 'composer.json');

        while (!\file_exists($path->toString())) {
            if ($dir === \dirname($dir)) {
                return $rootDir;
            }
            $dir = \dirname($dir);
            $path = Path::fromStrings($dir, 'composer.json');
        }

        return $dir;
    }
}
