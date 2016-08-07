<?php

namespace Crunz\Configuration;

use Crunz\Configuration\ConfigurationFactory as ConfigFactory;

trait Configurable {

    /**
     * Configuration object
     *
     * @var \Crunz\Configuration\Configuration
     */
    protected $config = null;

    /**
     * Create an instance of Configuration
     *
     */
    protected function configurable()
    {
        $this->config = ConfigFactory::makeOne();
    }

    /**
     * Return a configuration value by key
     *
     * @param  string $key
     *
     * @return string
     */
    protected function config($key)
    {
        if (is_null($this->config)) {
            return;
        }

        return $this->config->get($key);
    }

}