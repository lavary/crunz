<?php

namespace Crunz\Path;

use Crunz\Exception\CrunzException;

final class Path
{
    private $path;

    private function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return Path
     *
     * @throws CrunzException
     */
    public static function create(array $parts)
    {
        if (0 === \count($parts)) {
            throw new CrunzException('At least one part expected.');
        }

        $normalizedPath = \str_replace(
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            \implode(DIRECTORY_SEPARATOR, $parts)
        );

        return new self($normalizedPath);
    }

    /**
     * @param string ...$parts
     *
     * @return Path
     *
     * @throws CrunzException
     */
    public static function fromStrings(...$parts)
    {
        return self::create($parts);
    }

    public function toString()
    {
        return $this->path;
    }
}
