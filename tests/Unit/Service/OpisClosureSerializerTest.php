<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Service;

use Crunz\Application\Service\ClosureSerializerInterface;
use Crunz\Infrastructure\Opis\Closure\OpisClosureSerializer;

final class OpisClosureSerializerTest extends AbstractClosureSerializerTest
{
    protected function createSerializer(): ClosureSerializerInterface
    {
        return new OpisClosureSerializer();
    }
}
