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
