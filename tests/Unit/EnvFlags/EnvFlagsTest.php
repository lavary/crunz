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
    public function deprecation_handler_status_is_correct(string $flagValue, bool $expectedEnabled): void
    {
        \putenv(EnvFlags::DEPRECATION_HANDLER_FLAG . "={$flagValue}");

        $envFlags = new EnvFlags();
        $this->assertSame($expectedEnabled, $envFlags->isDeprecationHandlerEnabled());
    }

    /** @test */
    public function deprecation_handler_can_be_disabled(): void
    {
        $envFlags = new EnvFlags();
        $envFlags->disableDeprecationHandler();

        $this->assertFlagValue(EnvFlags::DEPRECATION_HANDLER_FLAG, '0');
    }

    /** @test */
    public function deprecation_handler_can_be_enabled(): void
    {
        $envFlags = new EnvFlags();
        $envFlags->enableDeprecationHandler();

        $this->assertFlagValue(EnvFlags::DEPRECATION_HANDLER_FLAG, '1');
    }

    /**
     * @test
     * @dataProvider containerDebugProvider
     */
    public function container_debug_flag_is_correct(string $flagValue, bool $expectedEnabled): void
    {
        \putenv(EnvFlags::CONTAINER_DEBUG_FLAG . "={$flagValue}");

        $envFlags = new EnvFlags();
        $this->assertSame($expectedEnabled, $envFlags->isContainerDebugEnabled());
    }

    /** @test */
    public function container_debug_can_be_disabled(): void
    {
        $envFlags = new EnvFlags();
        $envFlags->disableContainerDebug();

        $this->assertFlagValue(EnvFlags::CONTAINER_DEBUG_FLAG, '0');
    }

    /** @test */
    public function container_debug_can_be_enabled(): void
    {
        $envFlags = new EnvFlags();
        $envFlags->enableContainerDebug();

        $this->assertFlagValue(EnvFlags::CONTAINER_DEBUG_FLAG, '1');
    }

    /** @return iterable<string,array> */
    public function statusProvider(): iterable
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

    /** @return iterable<string,array> */
    public function containerDebugProvider(): iterable
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

    private function assertFlagValue(string $flag, string $expectedValue): void
    {
        $actualValue = \getenv($flag);
        $this->assertSame($expectedValue, $actualValue);
    }
}
