<?php

namespace Crunz\Tests\Unit\Filesystem;

use Crunz\Filesystem\Filesystem;
use Crunz\Path\Path;
use Crunz\Tests\TestCase\TemporaryFile;
use PHPUnit\Framework\TestCase;

final class FilesystemTest extends TestCase
{
    /** @test */
    public function cwdIsCorrect()
    {
        $filesystem = new Filesystem();

        $this->assertSame(\getcwd(), $filesystem->getCwd());
    }

    /**
     * @dataProvider fileExistsProvider
     * @test
     */
    public function fileExistsIsCorrect($path, $expectedExistence)
    {
        $filesystem = new Filesystem();

        $this->assertSame($expectedExistence, $filesystem->fileExists($path));
    }

    /** @test */
    public function tempDirectoryReturnSystemTempDirectory()
    {
        $filesystem = new Filesystem();

        $this->assertSame(\sys_get_temp_dir(), $filesystem->tempDir());
    }

    /** @test */
    public function removeDirectoryRemovesDirectoriesRecursively()
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
    public function dumpFileWritesContentToFile()
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
    public function createDirectoryCreatesDirectoryRecursive()
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
}
