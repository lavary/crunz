<?php

declare(strict_types=1);

namespace Crunz\Task;

use Crunz\Configuration\Configuration;
use Crunz\Finder\FinderInterface;
use Crunz\Path\Path;

class Collection
{
    /** @var Configuration */
    private $configuration;
    /** @var FinderInterface */
    private $finder;

    public function __construct(Configuration $configuration, FinderInterface $finder)
    {
        $this->configuration = $configuration;
        $this->finder = $finder;
    }

    /**
     * @param string $source
     *
     * @return \SplFileInfo[]
     */
    public function all($source)
    {
        if (!\file_exists($source)) {
            return [];
        }

        $suffix = $this->configuration
            ->get('suffix')
        ;
        $sourcePath = Path::create(
            [
                $source,
                "*{$suffix}",
            ]
        );

        return $this->finder
            ->find($sourcePath)
        ;
    }
}
