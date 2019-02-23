<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\EnvFlags;

use Crunz\EnvFlags\EnvFlags;
use PHPUnit\Framework\TestCase;

final class EnvFlagsTest extends TestCase
{
    /**
     * @test
     * @dataProvider statusProvider
     */
    public function deprecationHandlerStatusIsCorrect($flagValue, $expectedEnabled): void
    {
        \putenv(EnvFlags::DEPRECATION_HANDLER_FLAG . "={$flagValue}");

        $envFlags = new EnvFlags();
        $this->assertSame($expectedEnabled, $envFlags->isDeprecationHandlerEnabled());
    }

    /** @test */
    public function deprecationHandlerCanBeDisabled(): void
    {
        $envFlags = new EnvFlags();
        $envFlags->disableDeprecationHandler();

        $this->assertFlagValue(EnvFlags::DEPRECATION_HANDLER_FLAG, '0');
    }

    /** @test */
    public function deprecationHandlerCanBeEnabled(): void
    {
        $envFlags = new EnvFlags();
        $envFlags->enableDeprecationHandler();

        $this->assertFlagValue(EnvFlags::DEPRECATION_HANDLER_FLAG, '1');
    }

    /**
     * @test
     * @dataProvider containerDebugProvider
     */
    public function containerDebugFlagIsCorrect($flagValue, $expectedEnabled): void
    {
        \putenv(EnvFlags::CONTAINER_DEBUG_FLAG . "={$flagValue}");

        $envFlags = new EnvFlags();
        $this->assertSame($expectedEnabled, $envFlags->isContainerDebugEnabled());
    }

    /** @test */
    public function containerDebugCanBeDisabled(): void
    {
        $envFlags = new EnvFlags();
        $envFlags->disableContainerDebug();

        $this->assertFlagValue(EnvFlags::CONTAINER_DEBUG_FLAG, '0');
    }

    /** @test */
    public function containerDebugCanBeEnabled(): void
    {
        $envFlags = new EnvFlags();
        $envFlags->enableContainerDebug();

        $this->assertFlagValue(EnvFlags::CONTAINER_DEBUG_FLAG, '1');
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

    public function containerDebugProvider()
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

    private function assertFlagValue($flag, $expectedValue): void
    {
        $actualValue = \getenv($flag);
        $this->assertSame($expectedValue, $actualValue);
    }
}
