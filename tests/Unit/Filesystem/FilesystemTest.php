<?php

namespace Crunz\Tests\Unit\Filesystem;

use Crunz\Filesystem\Filesystem;
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
