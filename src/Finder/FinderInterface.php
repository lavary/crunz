<?php

namespace Crunz\Finder;

use Crunz\Path\Path;

interface FinderInterface
{
    /** @return \SplFileInfo[] */
    public function find(Path $path);
}
