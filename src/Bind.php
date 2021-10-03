<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer;

use Closure;

interface Bind
{
    /**
     * Method binding.
     *
     * @param string|null  $method
     * @param Closure|null $override
     *
     * @return self|null
     */
    public function method(string $method = null, Closure $override = null): self|null;

    /**
     * Basic "bind".
     *
     * @param Closure|string|null $concrete
     * @param bool                $shared
     *
     * @return self
     */
    public function to(Closure|string $concrete = null, bool $shared = false): self;

    /**
     * Basic "bind", binds itself.
     *
     * @return void
     */
    public function itself(): void;

    /**
     * Singleton.
     *
     * @param Closure|string|null $concrete
     *
     * @return void
     */
    public function singleton(Closure|string $concrete = null): void;

    /**
     * Scoped instance.
     *
     * @param Closure|string|null $concrete
     *
     * @return void
     */
    public function scoped(Closure|string $concrete = null): void;

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
}
