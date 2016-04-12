<?php

use Crunz\Utils;

if (!function_exists('split_camel')) {
    /**
     * Split camel case words to a sentence
     *
     * @param  string $text
     * @return string
     */
    function split_camel($text) {
        return Utils::splitCamel($text); 
    }
}

if (!function_exists('word2number')) {
    /**
     * Convert words to numbers
     *
     * @param  string $text
     * @return string
     */
    function word2number($text) {
        return Utils::wordToNumber($text); 
    }
}