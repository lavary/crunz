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

    /** @return string */
    public function tempDir()
    {
        return \sys_get_temp_dir();
    }
}
