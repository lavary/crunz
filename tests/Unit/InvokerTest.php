<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit;

use Crunz\Invoker;
use PHPUnit\Framework\TestCase;

class InvokerTest extends TestCase
{
    /** @test */
    public function call_executes_closure(): void
    {
        $i = 1;

        $invoker = new Invoker();
        $result = $invoker->call(
            function () use (&$i) {
                return ++$i;
            }
        );

        $this->assertSame(2, $i);
        $this->assertSame(2, $result);
    }

    /** @test */
    public function call_executes_closure_with_params(): void
    {
        $i = 1;

        $invoker = new Invoker();
        $invoker->call(
            function ($number) use (&$i): void {
                $i += $number;
            },
            [2]
        );

        $this->assertSame(3, $i);
    }

    /** @test */
    public function call_can_catch_output(): void
    {
        $invoker = new Invoker();
        $result = $invoker->call(
            function (): void {
                echo 'Callback was called, nice.';
            },
            [],
            true
        );

        $this->assertSame('Callback was called, nice.', $result);
    }
}
