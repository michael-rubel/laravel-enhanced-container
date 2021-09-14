<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall\Concerns;

use Closure;

interface MethodBinding
{
    /**
     * Bind the method.
     *
     * @param string  $method
     * @param Closure $callback
     *
     * @return void
     */
    public function bind(string $method, Closure $callback): void;
}
