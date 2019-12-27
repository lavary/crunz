<?php

declare(strict_types=1);

namespace Crunz;

class Invoker
{
    /**
     * Call the given Closure with buffering support.
     *
     * @param callable           $closure
     * @param bool               $buffer
     * @param array<mixed,mixed> $parameters
     *
     * @return mixed
     */
    public function call($closure, array $parameters = [], $buffer = false)
    {
        if ($buffer) {
            \ob_start();
        }

        $rslt = \call_user_func_array($closure, $parameters);

        if ($buffer) {
            return \ob_get_clean();
        }

        return $rslt;
    }
}
