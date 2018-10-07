<?php

declare(strict_types=1);

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
}
