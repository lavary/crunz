<?php

declare(strict_types=1);

namespace Crunz\Infrastructure\Opis\Closure;

use Crunz\Application\Service\ClosureSerializerInterface;
use Opis\Closure\SerializableClosure;

final class OpisClosureSerializer implements ClosureSerializerInterface
{
    public function serialize(\Closure $closure): string
    {
        return \serialize(
            new SerializableClosure(
                $closure
            )
        );
    }

    public function unserialize(string $serializedClosure): \Closure
    {
        $wrapper = $this->extractWrapper($serializedClosure);

        return $wrapper->getClosure();
    }

    private function extractWrapper(string $serializedClosure): SerializableClosure
    {
        return \unserialize(
            $serializedClosure,
            ['allowed_classes' => true]
        );
    }
}
