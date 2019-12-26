<?php

declare(strict_types=1);

namespace Crunz\Path;

use Crunz\Exception\CrunzException;

final class Path
{
    /** @var string */
    private $path;

    private function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param string[] $parts
     *
     * @return Path
     *
     * @throws CrunzException
     */
    public static function create(array $parts): self
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
     * @return Path
     *
     * @throws CrunzException
     */
    public static function fromStrings(string ...$parts): self
    {
        return self::create($parts);
    }

    public function toString(): string
    {
        return $this->path;
    }
}
