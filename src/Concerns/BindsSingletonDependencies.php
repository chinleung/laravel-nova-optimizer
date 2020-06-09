<?php

namespace ChinLeung\LaravelNovaOptimizer\Concerns;

use ChinLeung\LaravelNovaOptimizer\Exceptions\DependenciesNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait BindsSingletonDependencies
{
    /**
     * Keep track of the dependencies that has been bound.
     *
     * @var array
     */
    protected static $boundSingletonDependencies = [];

    /**
     * Bind the dependencies.
     *
     * @param  string  $name
     * @param  array  $arguments
     * @return void
     */
    public function __call($name, $arguments)
    {
        if ($this->isNotABindDependenciesMethod($name)) {
            trigger_error(
                sprintf(
                    'Call to undefined function: %s::%s().',
                    get_class($this),
                    $name
                ),
                E_USER_ERROR
            );
        }

        $this->bindDependencies(Str::between($name, 'bind', 'Dependencies'));
    }

    /**
     * Bind the dependencies for a key.
     *
     * @param  string  $key
     * @return void
     */
    protected function bindDependencies(string $key): void
    {
        if ($this->hasBoundDependency($key)) {
            return;
        }

        if (! method_exists($this, $method = "get{$key}Dependencies")) {
            throw new DependenciesNotFoundException($key);
        }

        collect($this->{$method}())
            ->reject(fn ($closure, $key) => app()->has($key))
            ->each(fn ($closure, $key) => app()->singleton($key, $closure));

        $this->markDependencyAsBound($key);
    }

    /**
     * Check if the dependency for a key has been bound.
     *
     * @param  string  $key
     * @return bool
     */
    protected function hasBoundDependency(string $key): bool
    {
        return Arr::has(static::$boundSingletonDependencies, $key);
    }

    /**
     * Mark a dependency as bound.
     *
     * @param  string  $key
     * @return self
     */
    protected function markDependencyAsBound(string $key): self
    {
        Arr::set(static::$boundSingletonDependencies, $key, true);

        return $this;
    }

    /**
     * Check if the method name matches the pattern for binding dependencies.
     *
     * @param  string  $name
     * @return bool
     */
    protected function isNotABindDependenciesMethod(string $name): bool
    {
        return ! Str::startsWith($name, 'bind')
            || ! Str::endsWith($name, 'Dependencies');
    }
}
