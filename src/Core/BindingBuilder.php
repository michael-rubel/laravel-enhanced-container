<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use Closure;
use MichaelRubel\EnhancedContainer\Bind;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;

class BindingBuilder implements Bind
{
    use HelpsProxies;

    /**
     * BindingBuilder constructor.
     *
     * @param object|string $class
     */
    public function __construct(
        private object | string $class
    ) {
    }

    /**
     * Pass the call through container.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return void
     * @throws \ReflectionException
     */
    public function __call(string $method, array $parameters): void
    {
        app()->bindMethod([
            $this->resolvePassedClass($this->class)::class,
            $method,
        ], current($parameters));
    }

    /**
     * Syntax sugar.
     *
     * @param string|null  $method
     * @param Closure|null $override
     *
     * @return $this|null
     */
    public function method(string $method = null, Closure $override = null): self|null
    {
        if (is_null($method) || is_null($override)) {
            return $this;
        }

        return $this->{$method}($override);
    }

    /**
     * Syntax sugar - basic "bind".
     *
     * @param Closure|string|null $concrete
     * @param bool                $shared
     *
     * @return void
     */
    public function to(Closure|string $concrete = null, bool $shared = false): void
    {
        app()->bind($this->class, $concrete, $shared);
    }

    /**
     * Syntax sugar - singleton.
     *
     * @param Closure|string|null $concrete
     *
     * @return void
     */
    public function singleton(Closure|string $concrete = null): void
    {
        app()->singleton($this->class, $concrete);
    }

    /**
     * Syntax sugar - scoped instance.
     *
     * @param Closure|string|null $concrete
     *
     * @return void
     */
    public function scoped(Closure|string $concrete = null): void
    {
        app()->scoped($this->class, $concrete);
    }
}
