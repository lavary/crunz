<?php

declare(strict_types=1);
/*
 * A copy from Symfony 3.4 with deprecations removed
 * since escapeArgument() method has been removed in 4.0.
 *
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Crunz;

use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Process\Process;

/**
 * ProcessUtils is a bunch of utility methods.
 *
 * This class contains static methods only and is not meant to be instantiated.
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 *
 * @todo Remove this class as it is deprecated in Symfony 3.3
 */
class ProcessUtils
{
    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Escapes a string to be used as a shell argument.
     *
     * @param string $argument The argument that will be escaped
     *
     * @return string The escaped argument
     */
    public static function escapeArgument($argument)
    {
        //Fix for PHP bug #43784 escapeshellarg removes % from given string
        //Fix for PHP bug #49446 escapeshellarg doesn't work on Windows
        //@see https://bugs.php.net/bug.php?id=43784
        //@see https://bugs.php.net/bug.php?id=49446
        if ('\\' === DIRECTORY_SEPARATOR) {
            if ('' === $argument) {
                return \escapeshellarg($argument);
            }

            $escapedArgument = '';
            $quote = false;
            /** @var string[] $parts */
            $parts = \preg_split('/(")/', $argument, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

            foreach ($parts as $part) {
                if ('"' === $part) {
                    $escapedArgument .= '\\"';
                } elseif (self::isSurroundedBy($part, '%')) {
                    // Avoid environment variable expansion
                    $escapedArgument .= '^%"' . \mb_substr($part, 1, -1) . '"^%';
                } else {
                    // escape trailing backslash
                    if ('\\' === \mb_substr($part, -1)) {
                        $part .= '\\';
                    }
                    $quote = true;
                    $escapedArgument .= $part;
                }
            }
            if ($quote) {
                $escapedArgument = '"' . $escapedArgument . '"';
            }

            return $escapedArgument;
        }

        return "'" . \str_replace("'", "'\\''", $argument) . "'";
    }

    /**
     * Validates and normalizes a Process input.
     *
     * @param string $caller The name of method call that validates the input
     * @param mixed  $input  The input to validate
     *
     * @return mixed The validated input
     *
     * @throws InvalidArgumentException In case the input is not valid
     */
    public static function validateInput($caller, $input)
    {
        if (null !== $input) {
            if (\is_resource($input)) {
                return $input;
            }
            if (\is_string($input)) {
                return $input;
            }
            if (\is_scalar($input)) {
                return (string) $input;
            }
            if ($input instanceof Process) {
                return $input->getIterator($input::ITER_SKIP_ERR);
            }
            if ($input instanceof \Iterator) {
                return $input;
            }
            if ($input instanceof \Traversable) {
                return new \IteratorIterator($input);
            }

            throw new InvalidArgumentException(\sprintf('%s only accepts strings, Traversable objects or stream resources.', $caller));
        }

        return $input;
    }

    private static function isSurroundedBy(string $arg, string $char): bool
    {
        return 2 < \mb_strlen($arg) && $char === $arg[0] && $char === $arg[\mb_strlen($arg) - 1];
    }
}
