<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use MichaelRubel\EnhancedContainer\Traits\InteractsWithContainer;

class MethodBinder
{
    use InteractsWithContainer;

    /**
     * @var string
     */
    protected string $abstract;

    /**
     * MethodBinder constructor.
     *
     * @param  object|string  $abstract
     */
    public function __construct(object|string $abstract)
    {
        $this->abstract = $this->convertToNamespace($abstract);
    }

    /**
     * Method binding.
     *
     * @param  string|null  $method
     * @param  \Closure|null  $override
     *
     * @return static|null
     */
    public function method(string $method = null, \Closure $override = null): ?static
    {
        $this->resolve();

        if (is_null($method) || is_null($override)) {
            return $this;
        }

        return $this->{$method}($override);
    }

    /**
     * Try to resolve an implementation for this particular abstract type.
     *
     * @return mixed
     */
    protected function resolve(): mixed
    {
        if (app()->bound($this->abstract)) {
            $concrete = app($this->abstract);

            $this->abstract = $this->convertToNamespace($concrete);
        }

        return $this->abstract;
    }

    /**
     * Bind the method to the container.
     *
     * @param  string  $method
     * @param  array  $parameters
     *
     * @return void
     */
    public function __call(string $method, array $parameters): void
    {
        app()->bindMethod([$this->abstract, $method], current($parameters));
    }
}
