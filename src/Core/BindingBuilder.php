<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use MichaelRubel\EnhancedContainer\Bind;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;

class BindingBuilder implements Bind
{
    use HelpsProxies;

    /**
     * @var \Closure|string|array
     */
    private \Closure|string|array $contextualImplementation;

    /**
     * BindingBuilder constructor.
     *
     * @param object|string $abstract
     */
    public function __construct(
        private object | string $abstract
    ) {
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
     * Bind the method to the container.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return void
     */
    public function __call(string $method, array $parameters): void
    {
        if (interface_exists($this->convertToNamespace($this->abstract))) {
            // auto-resolve implementation if it is an interface
            $this->abstract = resolve($this->abstract);
        }

        app()->bindMethod([
            $this->convertToNamespace($this->abstract),
            $method,
        ], current($parameters));
    }
}
