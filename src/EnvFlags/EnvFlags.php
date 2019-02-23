<?php

declare(strict_types=1);

namespace Crunz\EnvFlags;

use Crunz\Exception\CrunzException;

final class EnvFlags
{
    const DEPRECATION_HANDLER_FLAG = 'CRUNZ_DEPRECATION_HANDLER';
    const CONTAINER_DEBUG_FLAG = 'CRUNZ_CONTAINER_DEBUG';

    /** @return bool */
    public function isDeprecationHandlerEnabled()
    {
        $registerHandlerEnv = \getenv(self::DEPRECATION_HANDLER_FLAG, true);
        $registerHandler = true;

        if (false !== $registerHandlerEnv) {
            $registerHandler = \filter_var($registerHandlerEnv, FILTER_VALIDATE_BOOLEAN);
        }

        return $registerHandler;
    }

    /** @return bool */
    public function isContainerDebugEnabled()
    {
        $containerDebugEnv = \getenv(self::CONTAINER_DEBUG_FLAG, true);
        $containerDebug = false;

        if (false !== $containerDebugEnv) {
            $containerDebug = \filter_var($containerDebugEnv, FILTER_VALIDATE_BOOLEAN);
        }

        return $containerDebug;
    }

    /** @throws CrunzException When disabling deprecation handler fails */
    public function disableContainerDebug(): void
    {
        if (false === \putenv(self::CONTAINER_DEBUG_FLAG . '=0')) {
            throw new CrunzException('Disabling container debug failed.');
        }
    }

    /** @throws CrunzException When enabling deprecation handler fails */
    public function enableContainerDebug(): void
    {
        if (false === \putenv(self::CONTAINER_DEBUG_FLAG . '=1')) {
            throw new CrunzException('Enabling container debug failed.');
        }
    }

    /** @throws CrunzException When enabling deprecation handler fails */
    public function enableDeprecationHandler(): void
    {
        if (false === \putenv(self::DEPRECATION_HANDLER_FLAG . '=1')) {
            throw new CrunzException('Enabling deprecation handler failed.');
        }
    }

    /** @throws CrunzException When disabling deprecation handler fails */
    public function disableDeprecationHandler(): void
    {
        if (false === \putenv(self::DEPRECATION_HANDLER_FLAG . '=0')) {
            throw new CrunzException('Disabling deprecation handler failed.');
        }
    }
}
