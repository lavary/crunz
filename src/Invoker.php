<?php

namespace Crunz;

class Invoker
{
    
    /**
     * Call the given Closure
     *
     * @param  callable  $callback
     * @param  array     $parameters
     * @return mixed
     */
    public function call($callback, array $parameters = [])
    {
        return call_user_func_array($callback, $parameters);
    }

    

}