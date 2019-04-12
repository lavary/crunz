<?php

declare(strict_types=1);

namespace Crunz\Finder;

use Crunz\Path\Path;

final class Finder implements FinderInterface
{
    /**
     * {@inheritdoc}
     */
    public function find(Path $path)
    {
        /** @var \SplFileInfo[] $globIterator */
        $globIterator = new \GlobIterator(
            $path->toString(),
            \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS
        );

        return $globIterator;
    }
}
