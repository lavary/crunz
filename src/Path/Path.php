<?php

declare(strict_types=1);

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

        return new self(
            \implode(
                DIRECTORY_SEPARATOR,
                $parts
            )
        );
    }

    public function toString()
    {
        return $this->path;
    }
}
