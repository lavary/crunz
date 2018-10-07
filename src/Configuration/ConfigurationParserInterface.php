<?php

declare(strict_types=1);

namespace Crunz\Configuration;

interface ConfigurationParserInterface
{
    /** @return array */
    public function parseConfig();
}
