<?php

namespace ChinLeung\LaravelNovaOptimizer\Exceptions;

use Exception;

class DependenciesNotFoundException extends Exception
{
    /**
     * Create a new instance of the exception.
     *
     * @param  string  $key
     */
    public function __construct(string $key)
    {
        parent::__construct("Unable to load the dependencies for {$key}! Please make sure you have a get{$key}Dependencies method.");
    }
}
