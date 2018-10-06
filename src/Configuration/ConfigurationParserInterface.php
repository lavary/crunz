<?php

namespace Crunz\Configuration;

interface ConfigurationParserInterface
{
    /** @return array */
    public function parseConfig();
}
