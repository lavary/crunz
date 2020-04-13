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

    public function filePath(): string
    {
        return $this->filePath;
    }

    /** @param int $mode */
    public function changePermissions($mode): void
    {
        $this->checkFileExists();

        \chmod($this->filePath, $mode);
    }

    public function contents(): string
    {
        $this->checkFileExists();

        $content = \file_get_contents($this->filePath);

        if (false === $content) {
            throw new CrunzException("Unable to read from temporary file '{$this->filePath}'.");
        }

        return $content;
    }

    private function checkFileExists(): void
    {
        if (!\file_exists($this->filePath)) {
            throw new CrunzException("Temporary file '{$this->filePath}' no longer exists.");
        }
    }
}
