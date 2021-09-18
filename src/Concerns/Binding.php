<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Concerns;

use Closure;

interface Binding
{
    /**
     * Syntax sugar - method binding.
     *
     * @param string|null  $method
     * @param Closure|null $override
     *
     * @return self|null
     */
    public function method(string $method = null, Closure $override = null): self|null;

    /**
     * Syntax sugar - basic "bind".
     *
     * @param Closure|string|null $concrete
     * @param bool                $shared
     *
     * @return void
     */
    public function to(Closure|string $concrete = null, bool $shared = false): void;

    /**
     * Syntax sugar - singleton.
     *
     * @param Closure|string|null $concrete
     *
     * @return void
     */
    public function singleton(Closure|string $concrete = null): void;

    /**
     * Syntax sugar - scoped instance.
     *
     * @param Closure|string|null $concrete
     *
     * @return void
     */
    public function scoped(Closure|string $concrete = null): void;
}
