<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer;

use MichaelRubel\EnhancedContainer\Core\BindingBuilder;

interface Binding
{
    /**
     * Method binding.
     *
     * @param string|null  $method
     * @param \Closure|null $override
     *
     * @return $this|null
     */
    public function method(string $method = null, \Closure $override = null): self|null;

    /**
     * Basic "bind".
     *
     * @param object|string|null $concrete
     * @param bool                 $shared
     *
     * @return self
     */
    public function to(object|string $concrete = null, bool $shared = false): self;

    /**
     * Basic "bind", binds itself.
     *
     * @return void
     */
    public function itself(): void;

    /**
     * Singleton.
     *
     * @param object|string|null $concrete
     *
     * @return void
     */
    public function singleton(object|string $concrete = null): void;

    /**
     * Scoped instance.
     *
     * @param object|string|null $concrete
     *
     * @return void
     */
    public function scoped(object|string $concrete = null): void;

    /**
     * Enables contextual binding.
     *
     * @return $this
     */
    public function contextual(\Closure|string|array $implementation): self;

    /**
     * Contextual binding.
     *
     * @param array|string $concrete
     *
     * @return void
     */
    public function for(array|string $concrete): void;

    /**
     * Extend the abstract type.
     *
     * @param \Closure $closure
     *
     * @return BindingBuilder
     */
    public function extend(\Closure $closure): self;

    /**
     * Register an existing instance as shared in the container.
     *
     * @param mixed $instance
     *
     * @return BindingBuilder
     */
    public function instance(mixed $instance): self;
}
