<?php

namespace Crunz\Filesystem;

use Crunz\Path\Path;

final class Filesystem implements FilesystemInterface
{
    /** @var string */
    private $projectRootDir;

    /** {@inheritdoc} */
    public function getCwd()
    {
        return \getcwd();
    }

    /** {@inheritdoc} */
    public function fileExists($filePath)
    {
        return \file_exists($filePath);
    }

    /** {@inheritdoc} */
    public function tempDir()
    {
        return \sys_get_temp_dir();
    }

    /** {@inheritdoc} */
    public function removeDirectory($directoryPath, $ignoredPaths = [])
    {
        $ignoredCount = 0;
        $ignored = [];

        /** @var Path $ignoredPath */
        foreach ($ignoredPaths as $ignoredPath) {
            $path = Path::fromStrings($directoryPath, $ignoredPath->toString());
            $ignored[$path->toString()] = '';
        }

        $directoryIterator = new \RecursiveDirectoryIterator($directoryPath, \FilesystemIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator(
            $directoryIterator,
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $path */
        foreach ($recursiveIterator as $path) {
            if (\array_key_exists($path->getPathname(), $ignored)) {
                ++$ignoredCount;

                continue;
            }

            $path->isDir() && !$path->isLink()
                ? \rmdir($path->getPathname())
                : \unlink($path->getPathname())
            ;
        }

        if (0 === $ignoredCount) {
            \rmdir($directoryPath);
        }
    }

    /** {@inheritdoc} */
    public function dumpFile($filePath, $content)
    {
        \file_put_contents($filePath, $content);
    }

    /** {@inheritdoc} */
    public function createDirectory($directoryPath)
    {
        if ($this->fileExists($directoryPath)) {
            return;
        }

        $created = \mkdir(
            $directoryPath,
            0770,
            true
        );

        if (!$created && !\is_dir($directoryPath)) {
            throw new \RuntimeException("Directory '{$directoryPath}' was not created.");
        }
    }

    /**
     * @param string $sourceFile
     * @param string $targetFile
     */
    public function copy($sourceFile, $targetFile)
    {
        \copy($sourceFile, $targetFile);
    }

    /** {@inheritdoc} */
    public function projectRootDirectory()
    {
        if (null === $this->projectRootDir) {
            $dir = $rootDir = \dirname(__DIR__);
            $path = Path::fromStrings($dir, 'composer.json');

            while (!\file_exists($path->toString())) {
                if ($dir === \dirname($dir)) {
                    return $this->projectRootDir = $rootDir;
                }
                $dir = \dirname($dir);
                $path = Path::fromStrings($dir, 'composer.json');
            }

            $this->projectRootDir = $dir;
        }

        return $this->projectRootDir;
    }
}
