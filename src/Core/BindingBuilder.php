<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use MichaelRubel\EnhancedContainer\Bind;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;

class BindingBuilder implements Bind
{
    use HelpsProxies;

    /**
     * @var bool
     */
    private bool $isContextual = false;

    /**
     * @var \Closure|string|array
     */
    private \Closure|string|array $contextualImplementation;

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
        if (! $this->isContextual) {
            app()->bind($this->class, $concrete, $shared);
        }

        if ($this->isContextual && $concrete !== null) {
            $this->contextualImplementation = $concrete;
        }

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
        app()->singleton($this->class, $concrete);
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
        app()->scoped($this->class, $concrete);
    }

    /**
     * Enable isContextual binding.
     *
     * @return $this
     */
    public function asContextual(): self
    {
        $this->isContextual = true;

        return $this;
    }

    /**
     * Syntax sugar - isContextual binding.
     *
     * @param array|string $concrete
     *
     * @return void
     */
    public function when(array|string $concrete): void
    {
        app()->when($concrete)
            ->needs($this->class)
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
        if (interface_exists($this->convertToNamespace($this->class))) {
            $this->class = resolve($this->class);
        }

        app()->bindMethod([
            $this->convertToNamespace($this->class),
            $method,
        ], current($parameters));
    }
}
