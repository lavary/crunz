<?php

namespace Crunz\Filesystem;

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
}
