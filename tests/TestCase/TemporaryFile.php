<?php

declare(strict_types=1);

namespace Crunz\Tests\TestCase;

use Crunz\Exception\CrunzException;

final class TemporaryFile
{
    /** @var string */
    private $filePath;

    public function __construct()
    {
        $filePath = \tempnam(\sys_get_temp_dir(), 'ctf');

        if (false === $filePath) {
            throw new CrunzException('Unable to create temp file.');
        }

        $this->filePath = $filePath;
    }

    public function __destruct()
    {
        if (!\file_exists($this->filePath) || !\is_writable(\dirname($this->filePath))) {
            return;
        }

        \unlink($this->filePath);
    }

    /** @return string */
    public function filePath()
    {
        return $this->filePath;
    }
}
