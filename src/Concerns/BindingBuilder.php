<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall\Concerns;

use Closure;
use MichaelRubel\ContainerCall\Traits\HelpsProxies;

class BindingBuilder implements BindingBuilding
{
    use HelpsProxies;

    /**
     * @var Closure|string|null
     */
    private Closure|string|null $concrete = null;

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
    public function method(): self
    {
        return $this;
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
}
