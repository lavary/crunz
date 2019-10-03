<?php

declare(strict_types=1);

namespace Crunz\Task;

use Crunz\Schedule;

interface LoaderInterface
{
    /** @return Schedule[] */
    public function load(\SplFileInfo ...$files): array;
}
