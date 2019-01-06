<?php

namespace Crunz\EnvFlags;

use Crunz\Exception\CrunzException;

final class EnvFlags
{
    const DEPRECATION_HANDLER_FLAG = 'CRUNZ_DEPRECATION_HANDLER';

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

    /** @throws CrunzException When enabling deprecation handler fails */
    public function enableDeprecationHandler()
    {
        if (false === \putenv(self::DEPRECATION_HANDLER_FLAG . '=1')) {
            throw new CrunzException('Enabling deprecation handler failed.');
        }
    }

    /** @throws CrunzException When disabling deprecation handler fails */
    public function disableDeprecationHandler()
    {
        if (false === \putenv(self::DEPRECATION_HANDLER_FLAG . '=0')) {
            throw new CrunzException('Enabling deprecation handler failed.');
        }
    }
}
