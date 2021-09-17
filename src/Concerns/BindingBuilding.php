<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall\Concerns;

use Closure;

interface BindingBuilding
{
    /**
     * Syntax sugar.
     *
     * @return $this
     */
    public function method(): self;

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
