<?php

namespace Crunz\Task;

use Crunz\Configuration\Configuration;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Collection
{
    /** @var Configuration */
    private $configuration;
    /** @var Finder */
    private $finder;

    public function __construct(Configuration $configuration, Finder $finder)
    {
        $this->configuration = $configuration;
        $this->finder = $finder;
    }

    /**
     * @param string $source
     *
     * @return SplFileInfo[]|Finder
     */
    public function all($source)
    {
        if (!\file_exists($source)) {
            return [];
        }

        $suffix = $this->configuration
            ->get('suffix')
        ;

        $iterator = $this->finder
            ->files()
            ->name("*{$suffix}")
            ->in($source)
        ;

        return $iterator;
    }
}
