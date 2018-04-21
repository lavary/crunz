<?php

namespace Crunz\Configuration;

class ConfigurationFactory
{
    /**
     * Create an instance of Configuration class.
     *
     * @return \Configuration\Configuration
     */
    public static function makeOne()
    {
        return Configuration::getInstance();
    }
}
