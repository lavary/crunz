<?php

namespace Crunz;

class Singleton
{
    /**
     * Class instance.
     */
    protected static $instance = null;

    /**
     * Private clone method to prevent cloning of the instance of the
     * Singleton instance.
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     */
    private function __wakeup()
    {
    }

    /**
     * Return the instance of the class.
     *
     * @return mixed
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            return new static();
        }

        return static::$instance;
    }
}
