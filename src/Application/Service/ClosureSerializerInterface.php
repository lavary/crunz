<?php

declare(strict_types=1);

namespace Crunz\Application\Service;

interface ClosureSerializerInterface
{
    public function serialize(\Closure $closure): string;

    public function unserialize(string $serializedClosure): \Closure;

    public function closureCode(\Closure $closure): string;
}
