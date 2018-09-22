<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Pinger;

use Crunz\Pinger\PingableException;
use Crunz\Tests\Unit\Pingable;
use PHPUnit\Framework\TestCase;

final class PingableTest extends TestCase
{
    /**
     * @test
     * @dataProvider nonStringProvider
     */
    public function beforeUrlMustBeString($url)
    {
        $type = \gettype($url);
        $this->expectException(PingableException::class);
        $this->expectExceptionMessage("Url must be of type string, '{$type}' given.");

        $pingable = new Pingable();
        $pingable->pingBefore($url);
    }

    /**
     * @test
     */
    public function beforeUrlMustBeNonEmptyString()
    {
        $this->expectException(PingableException::class);
        $this->expectExceptionMessage('Url cannot be empty.');

        $pingable = new Pingable();
        $pingable->pingBefore('');
    }

    /**
     * @test
     */
    public function afterUrlMustBeNonEmptyString()
    {
        $this->expectException(PingableException::class);
        $this->expectExceptionMessage('Url cannot be empty.');

        $pingable = new Pingable();
        $pingable->thenPing('');
    }

    /**
     * @test
     * @dataProvider nonStringProvider
     */
    public function afterUrlMustBeString($url)
    {
        $type = \gettype($url);
        $this->expectException(PingableException::class);
        $this->expectExceptionMessage("Url must be of type string, '{$type}' given.");

        $pingable = new Pingable();
        $pingable->thenPing($url);
    }

    /** @test */
    public function getPingBeforeWithoutUrlFails()
    {
        $this->expectException(PingableException::class);
        $this->expectExceptionMessage('PingBeforeUrl is empty.');

        $pingable = new Pingable();
        $pingable->getPingBeforeUrl();
    }

    /** @test */
    public function getPingAfterWithoutUrlFails()
    {
        $this->expectException(PingableException::class);
        $this->expectExceptionMessage('PingAfterUrl is empty.');

        $pingable = new Pingable();
        $pingable->getPingAfterUrl();
    }

    /**
     * @return \Generator
     */
    public function nonStringProvider()
    {
        yield 'null' => [null];
        yield 'array' => [[]];
        yield 'object' => [new \stdClass()];
        yield 'int' => [123];
        yield 'float' => [6423.4324];
    }
}
