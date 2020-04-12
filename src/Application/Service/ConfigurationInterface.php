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

    public function getSourcePath(): string;
}
