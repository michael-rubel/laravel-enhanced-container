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
     * Syntax sugar.
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
     * Syntax sugar - basic "bind".
     *
     * @param \Closure|string|null $concrete
     * @param bool                $shared
     *
     * @return self
     */
    public function to(\Closure|string $concrete = null, bool $shared = false): self
    {
        app()->bind($this->abstract, $concrete, $shared);

        return $this;
    }

    /**
     * Syntax sugar - singleton.
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
     * Syntax sugar - scoped instance.
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
     * Enable contextual binding.
     *
     * @return $this
     */
    public function contextual(\Closure|string|array $implementation): self
    {
        $this->contextualImplementation = $implementation;

        return $this;
    }

    /**
     * Syntax sugar - contextual binding.
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
     * Pass the call through container.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return void
     */
    public function __call(string $method, array $parameters): void
    {
        if (interface_exists($this->convertToNamespace($this->abstract))) {
            $this->abstract = resolve($this->abstract);
        }

        app()->bindMethod([
            $this->convertToNamespace($this->abstract),
            $method,
        ], current($parameters));
    }
}
