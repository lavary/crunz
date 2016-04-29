<?php

namespace Crunz;
use Symfony\Component\Finder\Finder;

class TaskfileFinder
{
    
    /**
    * Collect all task files
    *
    * @param  string $source
    * @return Iterator
    */
    public static function collectFiles($source)
    {    
        if(!file_exists($source)) {
            return [];
        }
        
        $finder   = new Finder();
        $iterator = $finder->files()
                  ->name('*Tasks.php')
                  ->in($source);
        
        return $iterator;
    }

    

}