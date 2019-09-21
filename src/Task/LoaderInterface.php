<?php

namespace Crunz\Task;

use Crunz\Schedule;

interface LoaderInterface
{
    /** @return Schedule[] */
    public function load(\SplFileInfo ...$files);
}
