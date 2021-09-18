<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Concerns;

use Closure;

interface Binding
{
    /**
     * Syntax sugar.
     *
     * @param string|null  $method
     * @param Closure|null $override
     *
     * @return self|null
     */
    public function method(string $method = null, Closure $override = null): self|null;

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
