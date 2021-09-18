<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Concerns;

use Closure;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;

class BindingBuilder implements Binding
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
     * @return $this
     */
    public function method(string $method = null, Closure $override = null): mixed
    {
        if (is_null($method) || is_null($override)) {
            return $this;
        }

        return $this->{$method}($override);
    }

    /**
     * Syntax sugar.
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
     * Syntax sugar.
     *
     * @param Closure|string|null $concrete
     *
     * @return void
     */
    public function singleton(Closure|string $concrete = null): void
    {
        app()->singleton($this->class, $concrete);
    }
}
