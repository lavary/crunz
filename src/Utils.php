<?php

namespace Crunz;

final class Utils
{
    /**
     * Set project's root directory.
     *
     * @param string $autoloader
     *
     * @internal
     *
     * @return string
     */
    public static function setBaseDir($base_dir)
    {
        putenv('CRUNZ_BASE_DIR=' . $base_dir);
    }

    /**
     * Return project's root directory.
     *
     * @internal
     *
     * @return string
     */
    public static function getBaseDir()
    {
        return getenv('CRUNZ_BASE_DIR');
    }

    /**
     * Get the root directory by the autoloader file.
     *
     * @param string $autoloader
     *
     * @internal
     *
     * @return string
     */
    public static function getRoot($autoloader)
    {
        return dirname($autoloader) . DIRECTORY_SEPARATOR . '..';
    }

    /**
     * return absolute path for relative path.
     *
     * @param string $relative_path
     *
     * @internal
     *
     * @return string
     */
    public static function generatePath($relative_path)
    {
        return static::getBaseDir() . '/' . trim($relative_path, '/');
    }

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
    public static function arrayOnly($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Converts words to numbers.
     *
     * @param string $text
     *
     * @internal
     *
     * @return string
     */
    public static function wordToNumber($text)
    {
        $data = strtr(
            $text,
            [
                'zero' => '0',
                'a' => '1',
                'one' => '1',
                'two' => '2',
                'three' => '3',
                'four' => '4',
                'five' => '5',
                'six' => '6',
                'seven' => '7',
                'eight' => '8',
                'nine' => '9',
                'ten' => '10',
                'eleven' => '11',
                'twelve' => '12',
                'thirteen' => '13',
                'fourteen' => '14',
                'fifteen' => '15',
                'sixteen' => '16',
                'seventeen' => '17',
                'eighteen' => '18',
                'nineteen' => '19',
                'twenty' => '20',
                'thirty' => '30',
                'forty' => '40',
                'fourty' => '40',
                'fifty' => '50',
                'sixty' => '60',
                'seventy' => '70',
                'eighty' => '80',
                'ninety' => '90',
                'hundred' => '100',
                'thousand' => '1000',
                'million' => '1000000',
                'billion' => '1000000000',
                'and' => '',
            ]
        );

        // Coerce all tokens to numbers
        $parts = array_map(
            function ($val) {
                return floatval($val);
            },
            preg_split('/[\s-]+/', $data)
        );

        $tmp = null;
        $sum = 0;
        $last = null;

        foreach ($parts as $part) {
            if (!is_null($tmp)) {
                if ($tmp > $part) {
                    if ($last >= 1000) {
                        $sum += $tmp;
                        $tmp = $part;
                    } else {
                        $tmp = $tmp + $part;
                    }
                } else {
                    $tmp = $tmp * $part;
                }
            } else {
                $tmp = $part;
            }

            $last = $part;
        }

        return $sum + $tmp;
    }
}
