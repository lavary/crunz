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
     *
     * @param mixed $url
     * @dataProvider nonStringProvider
     */
    public function before_url_must_be_string($url): void
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
    public function before_url_must_be_non_empty_string(): void
    {
        $this->expectException(PingableException::class);
        $this->expectExceptionMessage('Url cannot be empty.');

        $pingable = new Pingable();
        $pingable->pingBefore('');
    }

    /**
     * @test
     */
    public function after_url_must_be_non_empty_string(): void
    {
        $this->expectException(PingableException::class);
        $this->expectExceptionMessage('Url cannot be empty.');

        $pingable = new Pingable();
        $pingable->thenPing('');
    }

    /**
     * @test
     *
     * @param mixed $url
     * @dataProvider nonStringProvider
     */
    public function after_url_must_be_string($url): void
    {
        $type = \gettype($url);
        $this->expectException(PingableException::class);
        $this->expectExceptionMessage("Url must be of type string, '{$type}' given.");

        $pingable = new Pingable();
        $pingable->thenPing($url);
    }

    /** @test */
    public function get_ping_before_without_url_fails(): void
    {
        $this->expectException(PingableException::class);
        $this->expectExceptionMessage('PingBeforeUrl is empty.');

        $pingable = new Pingable();
        $pingable->getPingBeforeUrl();
    }

    /** @test */
    public function get_ping_after_without_url_fails(): void
    {
        $this->expectException(PingableException::class);
        $this->expectExceptionMessage('PingAfterUrl is empty.');

        $pingable = new Pingable();
        $pingable->getPingAfterUrl();
    }

    /** @return iterable<string,array> */
    public function nonStringProvider(): iterable
    {
        yield 'null' => [null];
        yield 'array' => [[]];
        yield 'object' => [new \stdClass()];
        yield 'int' => [123];
        yield 'float' => [6423.4324];
    }
}
