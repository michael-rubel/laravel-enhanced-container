<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Concerns;

use Closure;

interface Binding
{
    /**
     * Syntax sugar.
     *
     * @return $this
     */
    public function method(string $method = null, Closure $override = null): mixed;

    /**
     * Syntax sugar.
     *
     * @param Closure|string|null $concrete
     * @param bool                $shared
     *
     * @return void
     */
    public function to(Closure|string $concrete = null, bool $shared = false): void;
}
