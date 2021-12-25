<?php

declare(strict_types=1);

use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Core\BindingBuilder;

if (! function_exists('call')) {
    /**
     * @param string|object $class
     * @param array         $parameters
     * @param string|null   $context
     *
     * @return mixed
     */
    function call(string|object $class, array $parameters = [], ?string $context = null): mixed
    {
        return app(Call::class, [$class, $parameters, $context]);
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
        return app(BindingBuilder::class, ['abstract' => $abstract]);
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

if (! function_exists('instance')) {
    /**
     * @param string $abstract
     * @param mixed  $instance
     *
     * @return void
     */
    function instance(string $abstract, mixed $instance): void
    {
        app()->instance($abstract, $instance);
    }
}

if (! function_exists('isForwardingEnabled')) {
    /**
     * @return bool
     */
    function isForwardingEnabled(): bool
    {
        return (bool) config('enhanced-container.forwarding_enabled');
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

if (! function_exists('runWithoutForwarding')) {
    /**
     * @param Closure $closure
     *
     * @return mixed
     */
    function runWithoutForwarding(\Closure $closure): mixed
    {
        disableMethodForwarding();

        $callback = $closure();

        enableMethodForwarding();

        return $callback;
    }
}

if (! function_exists('runWithForwarding')) {
    /**
     * @param Closure $closure
     *
     * @return mixed
     */
    function runWithForwarding(\Closure $closure): mixed
    {
        enableMethodForwarding();

        $callback = $closure();

        disableMethodForwarding();

        return $callback;
    }
}

if (! function_exists('single')) {
    /**
     * Checks if an array has only a single element.
     *
     * @param Countable|array $params
     *
     * @return bool
     */
    function single(\Countable|array $params): bool
    {
        return count($params) === 1;
    }
}
