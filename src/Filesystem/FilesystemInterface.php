<?php

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
    public function removeDirectory($directoryPath, $ignoredPaths = []);

    /**
     * @param string $filePath
     * @param string $content
     */
    public function dumpFile($filePath, $content);

    /** @param string $directoryPath */
    public function createDirectory($directoryPath);

    /**
     * @param string $sourceFile
     * @param string $targetFile
     */
    public function copy($sourceFile, $targetFile);

    /** @return string */
    public function projectRootDirectory();
}
