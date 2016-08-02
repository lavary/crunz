<?php

namespace Crunz;

class Invoker
{
    
    /**
     * Call the given Closure
     *
     * @param  callable  $callback
     * @param  array     $parameters
     *
     * @return mixed
     */
    public function call($closure, array $parameters = [])
    {
        return call_user_func_array($closure, $parameters);
    }

    

}