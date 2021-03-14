<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Finder;

use Crunz\Filesystem\Filesystem;
use Crunz\Finder\Finder;
use Crunz\Path\Path;
use PHPUnit\Framework\TestCase;

final class FinderTest extends TestCase
{
    /** @var Filesystem */
    private $filesystem;
    /** @var Path */
    private $tasksDirectory;
    /** @var Path */
    private $fixtureDirectory;

    public function setUp(): void
    {
        $filesystem = new Filesystem();
        $this->filesystem = $filesystem;
        $this->tasksDirectory = Path::fromStrings(
            $filesystem->tempDir(),
            '.crunz',
            'finder-test'
        );
        $this->filesystem->createDirectory($this->tasksDirectory->toString());
        $this->fixtureDirectory = Path::fromStrings(
            \dirname(__DIR__, 2),
            'resources',
            'fixtures',
            'finder',
            'direct'
        );
    }

    public function tearDown(): void
    {
        $tasksDirectory = $this->tasksDirectory;
        $this->filesystem
            ->removeDirectory($tasksDirectory->toString());
    }

    /**
     * @test
     * @dataProvider tasksProvider
     */
    public function find_returns_spl_file_info_collection(string $suffix, Path ...$files): void
    {
        $this->createFiles(...$files);
        $tasksDirectory = $this->tasksDirectory;

        $finder = new Finder();
        $foundFiles = $finder->find($tasksDirectory, $suffix);

        $this->assertCount(\count($files), $foundFiles);
        $this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foundFiles);
    }

    /**
     * @return iterable<string,array>
     *
     * @throws \Crunz\Exception\CrunzException
     */
    public function tasksProvider(): iterable
    {
        $suffix = 'Here.php';
        $taskOne = Path::fromStrings('TestHere.php');
        $taskTwo = Path::fromStrings('first-level', 'OtherTestHere.php');
        $taskThree = Path::fromStrings(
            'first-level',
            'second-level',
            'TestHere.php'
        );

        yield 'flat' => [$suffix, $taskOne];
        yield 'firstLevel' => [
            $suffix,
            $taskOne,
            $taskTwo,
        ];
        yield 'secondLevel' => [
            $suffix,
            $taskOne,
            $taskTwo,
            $taskThree,
        ];
    }

    /**
     * @test
     */
    public function find_files_in_symlinked_folder(): void
    {
        if ($this->isWindows()) {
            // Committed symlinks require extra steps to work on Windows
            // https://stackoverflow.com/questions/5917249/git-symlinks-in-windows
            $this->markTestSkipped('Required Unix-based OS.');
        }

        $fixtureDirectory = $this->fixtureDirectory;
        $directFile = Path::fromStrings($fixtureDirectory->toString(), 'directHere.php')->toString();
        $symlinkFileDestination = Path::fromStrings($fixtureDirectory->toString(), 'symlink', 'symlinkHere.php')->toString();

        $finder = new Finder();
        $foundFiles = $finder->find($fixtureDirectory, 'Here.php');

        $this->assertCount(2, $foundFiles);
        $this->assertArrayHasKey($directFile, $foundFiles);
        $this->assertArrayHasKey($symlinkFileDestination, $foundFiles);
    }

    private function createFiles(Path ...$files): void
    {
        $tasksDirectory = $this->tasksDirectory;

        foreach ($files as $file) {
            $path = Path::fromStrings($tasksDirectory->toString(), $file->toString());
            $content = \bin2hex(\random_bytes(8));
            $this->filesystem
                ->dumpFile($path->toString(), $content);
        }
    }

    private function isWindows(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }
}
