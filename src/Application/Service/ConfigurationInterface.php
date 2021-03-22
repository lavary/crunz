<?php

declare(strict_types=1);

namespace Crunz\Application\Service;

interface ConfigurationInterface
{
    /**
     * Return a parameter based on a key.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Set a parameter based on a key.
     *
     * @param mixed $value
     */
    public function withNewEntry(string $key, $value): ConfigurationInterface;

    public function getSourcePath(): string;
}
