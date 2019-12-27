<?php

declare(strict_types=1);

namespace Crunz\Configuration;

interface ConfigurationParserInterface
{
    /** @return array<string,int|string|array> */
    public function parseConfig(): array;
}
