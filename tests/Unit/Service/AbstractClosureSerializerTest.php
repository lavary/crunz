<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Service;

use Crunz\Application\Service\ClosureSerializerInterface;
use Crunz\Tests\TestCase\UnitTestCase;

abstract class AbstractClosureSerializerTest extends UnitTestCase
{
    public function test_closure_code_can_be_extracted(): void
    {
        // Arrange
        $testClosure = static function (): \stdClass {return new \stdClass(); };
        $serializer = $this->createSerializer();

        // Act
        $code = $serializer->closureCode($testClosure);

        // Assert
        $this->assertSame('static function (): \stdClass {return new \stdClass(); }', $code);
    }

    abstract protected function createSerializer(): ClosureSerializerInterface;
}
