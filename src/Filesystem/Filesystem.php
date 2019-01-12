<?php

namespace Crunz\Filesystem;

final class Filesystem implements FilesystemInterface
{
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
    public function removeDirectory($directoryPath)
    {
        $directoryIterator = new \RecursiveDirectoryIterator($directoryPath, \FilesystemIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator(
            $directoryIterator,
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($recursiveIterator as $path) {
            $path->isDir() && !$path->isLink()
                ? \rmdir($path->getPathname())
                : \unlink($path->getPathname())
            ;
        }

        \rmdir($directoryPath);
    }

    /** {@inheritdoc} */
    public function dumpFile($filePath, $content)
    {
        \file_put_contents($filePath, $content);
    }
}
