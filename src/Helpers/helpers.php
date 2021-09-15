<?php

declare(strict_types=1);

use MichaelRubel\ContainerCall\Call;
use MichaelRubel\ContainerCall\Concerns\MethodBinding;

if (! function_exists('call')) {
    /**
     * @param string|object $service
     * @param array         $parameters
     *
     * @return mixed
     */
    function call(string|object $service, array $parameters = []): mixed
    {
        return app(Call::class, [$service, $parameters]);
    }
}

if (! function_exists('bindMethod')) {
    /**
     * @param string|object $service
     * @param string        $method
     * @param Closure       $callback
     *
     * @return mixed
     */
    function bindMethod(string|object $service, string $method, Closure $callback): mixed
    {
        return app(MethodBinding::class, [$service, $method, $callback]);
    }
}
