<?php

declare(strict_types=1);

use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Core\BindingBuilder;

if (! function_exists('call')) {
    /**
     * @param string|object $class
     * @param array         $parameters
     *
     * @return mixed
     */
    function call(string|object $class, array $parameters = []): mixed
    {
        return app(Call::class, [$class, $parameters]);
    }
}

if (! function_exists('bind')) {
    /**
     * @param string|object $abstract
     *
     * @return BindingBuilder
     */
    function bind(string|object $abstract): BindingBuilder
    {
        return new BindingBuilder($abstract);
    }
}

if (! function_exists('singleton')) {
    /**
     * @param string              $abstract
     * @param Closure|string|null $concrete
     *
     * @return void
     */
    function singleton(string $abstract, \Closure|string $concrete = null): void
    {
        app()->singleton($abstract, $concrete);
    }
}

if (! function_exists('scoped')) {
    /**
     * @param string              $abstract
     * @param Closure|string|null $concrete
     *
     * @return void
     */
    function scoped(string $abstract, \Closure|string $concrete = null): void
    {
        app()->scoped($abstract, $concrete);
    }
}

if (! function_exists('extend')) {
    /**
     * @param string  $abstract
     * @param Closure $closure
     *
     * @return void
     */
    function extend(string $abstract, \Closure $closure): void
    {
        app()->extend($abstract, $closure);
    }
}

if (! function_exists('enableMethodForwarding')) {
    /**
     * @return void
     */
    function enableMethodForwarding(): void
    {
        config(['enhanced-container.forwarding_enabled' => true]);
    }
}

if (! function_exists('disableMethodForwarding')) {
    /**
     * @return void
     */
    function disableMethodForwarding(): void
    {
        config(['enhanced-container.forwarding_enabled' => false]);
    }
}
