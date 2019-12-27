<?php

declare(strict_types=1);

namespace Crunz\Filesystem;

use Crunz\Path\Path;

interface FilesystemInterface
{
    /** @return string */
    public function getCwd();

    /**
     * @param string $filePath
     *
     * @return bool
     */
    public function fileExists($filePath);

    /** @return string */
    public function tempDir();

    /**
     * @param string $directoryPath
     * @param Path[] $ignoredPaths
     */
    public function removeDirectory($directoryPath, $ignoredPaths = []): void;

    /**
     * @param string $filePath
     * @param string $content
     */
    public function dumpFile($filePath, $content): void;

    /** @param string $directoryPath */
    public function createDirectory($directoryPath): void;

    /**
     * @param string $sourceFile
     * @param string $targetFile
     */
    public function copy($sourceFile, $targetFile): void;

    /** @return string */
    public function projectRootDirectory();

    /**
     * @param string $filePath
     *
     * @return string
     */
    public function readContent($filePath);
}
