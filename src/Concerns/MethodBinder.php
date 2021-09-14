<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall\Concerns;

use Closure;
use MichaelRubel\ContainerCall\Traits\HelpsContainerCalls;

class MethodBinder implements MethodBinding
{
    use HelpsContainerCalls;

    /**
     * MethodBinder constructor.
     *
     * @param object|string $service
     */
    public function __construct(
        private object | string $service
    ) {
    }

    /**
     * Bind the method.
     *
     * @param string  $method
     * @param Closure $callback
     *
     * @return void
     */
    public function bind(string $method, Closure $callback): void
    {
        app()->bindMethod([
            $this->resolvePassedService($this->service)::class,
            $method,
        ], $callback);
    }
}
