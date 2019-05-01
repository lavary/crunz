<?php

declare(strict_types=1);

namespace Crunz\Finder;

use Crunz\Path\Path;

interface FinderInterface
{
    /**
     * @param string $suffix
     *
     * @return \SplFileInfo[]
     */
    public function find(Path $directory, $suffix);
}
