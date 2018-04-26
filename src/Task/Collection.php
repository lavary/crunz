<?php

namespace Crunz\Task;

use Crunz\Configuration\NonSingletonConfiguration;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Collection
{
    /** @var NonSingletonConfiguration */
    private $configuration;
    /** @var Finder */
    private $finder;

    public function __construct(NonSingletonConfiguration $configuration, Finder $finder)
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
