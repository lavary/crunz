<?php

namespace Crunz\Tests\Unit\EnvFlags;

use Crunz\EnvFlags\EnvFlags;
use PHPUnit\Framework\TestCase;

final class EnvFlagsTest extends TestCase
{
    /**
     * @test
     * @dataProvider statusProvider
     */
    public function deprecationHandlerStatusIsCorrect($flagValue, $expectedEnabled)
    {
        \putenv(EnvFlags::DEPRECATION_HANDLER_FLAG . "={$flagValue}");

        $envFlags = new EnvFlags();
        $this->assertSame($expectedEnabled, $envFlags->isDeprecationHandlerEnabled());
    }

    /** @test */
    public function deprecationHandlerCanBeDisabled()
    {
        $envFlags = new EnvFlags();
        $envFlags->disableDeprecationHandler();

        $this->assertFlagValue('0');
    }

    /** @test */
    public function deprecationHandlerCanBeEnabled()
    {
        $envFlags = new EnvFlags();
        $envFlags->enableDeprecationHandler();

        $this->assertFlagValue('1');
    }

    public function statusProvider()
    {
        yield 'true' => [
            '1',
            true,
        ];

        yield 'false' => [
            '0',
            false,
        ];
    }

    private function assertFlagValue($expectedValue)
    {
        $actualValue = \getenv(EnvFlags::DEPRECATION_HANDLER_FLAG);
        $this->assertSame($expectedValue, $actualValue);
    }
}
