<?php

namespace Crunz\Finder;

use Crunz\Path\Path;

final class Finder implements FinderInterface
{
    /**
     * {@inheritdoc}
     */
    public function find(Path $directory, $suffix)
    {
        $quotedSuffix = \preg_quote($suffix, '/');
        $directoryIterator = new \RecursiveDirectoryIterator($directory->toString());
        $recursiveIterator = new \RecursiveIteratorIterator($directoryIterator);

        $regexIterator = new \RegexIterator(
            $recursiveIterator,
            "/^.+{$quotedSuffix}$/i",
            \RecursiveRegexIterator::GET_MATCH
        );

        /** @var \SplFileInfo[] $files */
        $files = \array_map(
            static function (array $file) {
                return new \SplFileInfo(\reset($file));
            },
            \iterator_to_array($regexIterator)
        );

        return $files;
    }
}
