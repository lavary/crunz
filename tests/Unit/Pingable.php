<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit;

use Crunz\Pinger\PingableInterface;
use Crunz\Pinger\PingableTrait;

class Pingable implements PingableInterface
{
    use PingableTrait;
}
