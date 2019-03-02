<?php

declare(strict_types=1);

namespace Crunz\Configuration;

interface ConfigurationParserInterface
{
    public function parseConfig(): array;
}
