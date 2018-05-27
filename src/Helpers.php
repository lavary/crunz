<?php

use Crunz\Utils;

if (!function_exists('word2number')) {
    /**
     * Convert words to numbers.
     *
     * @param string $text
     *
     * @internal
     *
     * @return string
     */
    function word2number($text)
    {
        return Utils::wordToNumber($text);
    }
}

if (!function_exists('array_only')) {
    /**
     * Get a subset of the items from the given array
     * From Illuminate/support helper functions.
     *
     * @param array        $array
     * @param array|string $keys
     *
     * @internal
     *
     * @return array
     */
    function array_only($array, $keys)
    {
        return Utils::arrayOnly($array, $keys);
    }
}

if (!function_exists('setbase')) {
    /**
     * Set the project's root directory.
     *
     * @param string $dir
     *
     * @internal
     *
     * @return string
     */
    function setbase($dir)
    {
        return Utils::setBaseDir($dir);
    }
}

if (!function_exists('getbase')) {
    /**
     * Return the project's root directory.
     *
     * @internal
     *
     * @return string
     */
    function getbase()
    {
        return Utils::getBaseDir();
    }
}

if (!function_exists('generate_path')) {
    /**
     * Return absolute path for relative path.
     *
     * @param string $relative_path
     *
     * @internal
     *
     * @return string
     */
    function generate_path($relative_path)
    {
        return Utils::generatePath($relative_path);
    }
}

if (!function_exists('getroot')) {
    /**
     * Return absolute path for relative path.
     *
     * @param string $autoloader
     *
     * @internal
     *
     * @return string
     */
    function getroot($autoloader)
    {
        return Utils::getRoot($autoloader);
    }
}
