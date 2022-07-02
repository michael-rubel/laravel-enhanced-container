<?php

declare(strict_types=1);

use MichaelRubel\EnhancedContainer\Core\BindingBuilder;
use MichaelRubel\EnhancedContainer\Core\CallProxy;

if (! function_exists('call')) {
    /**
     * @param  string|object  $class
     * @param  array  $parameters
     * @param  string|null  $context
     *
     * @return CallProxy
     */
    function call(string|object $class, array $parameters = [], ?string $context = null): CallProxy
    {
        return app(CallProxy::class, [
            'class'        => $class,
            'dependencies' => $parameters,
            'context'      => $context,
        ]);
    }
}

if (! function_exists('bind')) {
    /**
     * @param  string|object  $abstract
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
     * @param  string  $abstract
     * @param  Closure|string|null  $concrete
     *
     * @return void
     */
    function singleton(string $abstract, Closure|string $concrete = null): void
    {
        app()->singleton($abstract, $concrete);
    }
}

if (! function_exists('scoped')) {
    /**
     * @param  string  $abstract
     * @param  Closure|string|null  $concrete
     *
     * @return void
     */
    function scoped(string $abstract, Closure|string $concrete = null): void
    {
        app()->scoped($abstract, $concrete);
    }
}

if (! function_exists('extend')) {
    /**
     * @param  string  $abstract
     * @param  Closure  $closure
     *
     * @return void
     */
    function extend(string $abstract, Closure $closure): void
    {
        app()->extend($abstract, $closure);
    }
}

if (! function_exists('instance')) {
    /**
     * @param  string  $abstract
     * @param  mixed  $instance
     *
     * @return void
     */
    function instance(string $abstract, mixed $instance): void
    {
        app()->instance($abstract, $instance);
    }
}

if (! function_exists('single')) {
    /**
     * Checks if an array has only a single element.
     *
     * @param  mixed  $params
     *
     * @return bool
     */
    function single(mixed $params): bool
    {
        return ! is_array($params) && ! $params instanceof \Countable
            ? $params == 1
            : count($params) === 1;
    }
}
