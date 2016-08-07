<?php

namespace Crunz;

use Crunz\Exception\CrunzException;

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
    public function call($closure, array $parameters = [], $buffer = true)
    {
        if ($buffer) {
            ob_start();
        }
        
        call_user_func_array($closure, $parameters);
            
        if ($buffer) {
            return ob_get_clean();
        }

    }
    


}