<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use MichaelRubel\EnhancedContainer\Bind;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;

class BindingBuilder implements Bind
{
    use HelpsProxies;

    /**
     * @var string
     */
    private string $abstract;

    /**
     * @var \Closure|string|array
     */
    private \Closure|string|array $contextualImplementation;

    /**
     * BindingBuilder constructor.
     *
     * @param object|string $abstract
     */
    public function __construct(object | string $abstract)
    {
        $this->abstract = $this->convertToNamespace($abstract);
    }

    /**
     * Method binding.
     *
     * @param string|null  $method
     * @param \Closure|null $override
     *
     * @return $this|null
     */
    public function method(string $method = null, \Closure $override = null): self|null
    {
        // try to resolve implementation
        // for actual abstract type
        $this->resolve();

        if (is_null($method) || is_null($override)) {
            return $this;
        }

        return $this->{$method}($override);
    }

    /**
     * Basic "bind".
     *
     * @param \Closure|string|null $concrete
     * @param bool                 $shared
     *
     * @return $this
     */
    public function to(\Closure|string $concrete = null, bool $shared = false): self
    {
        app()->bind($this->abstract, $concrete, $shared);

        return $this;
    }

    /**
     * Basic "bind", binds itself.
     *
     * @return void
     */
    public function itself(): void
    {
        app()->bind($this->abstract);
    }

    /**
     * Singleton.
     *
     * @param \Closure|string|null $concrete
     *
     * @return void
     */
    public function singleton(\Closure|string $concrete = null): void
    {
        app()->singleton($this->abstract, $concrete);
    }

    /**
     * Scoped instance.
     *
     * @param \Closure|string|null $concrete
     *
     * @return void
     */
    public function scoped(\Closure|string $concrete = null): void
    {
        app()->scoped($this->abstract, $concrete);
    }

    /**
     * Enables contextual binding.
     *
     * @return $this
     */
    public function contextual(\Closure|string|array $implementation): self
    {
        $this->contextualImplementation = $implementation;

        return $this;
    }

    /**
     * Contextual binding.
     *
     * @param array|string $concrete
     *
     * @return void
     */
    public function for(array|string $concrete): void
    {
        app()->when($concrete)
             ->needs($this->abstract)
             ->give($this->contextualImplementation);
    }

    /**
     * Extend the abstract type.
     *
     * @param \Closure $closure
     *
     * @return BindingBuilder
     */
    public function extend(\Closure $closure): self
    {
        app()->extend($this->abstract, $closure);

        return $this;
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param mixed $instance
     *
     * @return BindingBuilder
     */
    public function instance(mixed $instance): self
    {
        app()->instance($this->abstract, $instance);

        return $this;
    }

    /**
     * Try to resolve an abstract.
     *
     * @return mixed
     */
    public function resolve(): mixed
    {
        return rescue(
            fn () => $this->abstract = $this->convertToNamespace(
                resolve($this->abstract)
            ),
            null,
            false
        );
    }

    /**
     * Bind the method to the container.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return void
     */
    public function __call(string $method, array $parameters): void
    {
        app()->bindMethod([
            $this->abstract,
            $method,
        ], current($parameters));
    }
}
