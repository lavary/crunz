<?php

namespace Crunz\Tests\Unit;

use Crunz\Invoker;
use PHPUnit\Framework\TestCase;

class InvokerTest extends TestCase
{
    /** @test */
    public function callExecutesClosure()
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
    public function callExecutesClosureWithParams()
    {
        $i = 1;

        $invoker = new Invoker();
        $invoker->call(
            function ($number) use (&$i) {
                $i += $number;
            },
            [2]
        );

        $this->assertSame(3, $i);
    }

    /** @test */
    public function callCanCatchOutput()
    {
        $invoker = new Invoker();
        $result = $invoker->call(
            function () {
                echo 'Callback was called, nice.';
            },
            [],
            true
        );

        $this->assertSame('Callback was called, nice.', $result);
    }
}
