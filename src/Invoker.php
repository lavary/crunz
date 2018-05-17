<?php

namespace Crunz;

class Invoker
{
    /**
     * Call the given Closure with buffering support.
     *
     * @param callable $callback
     * @param array    $parameters
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
