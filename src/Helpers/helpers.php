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
     * @param  object|string  $abstract
     *
     * @return BindingBuilder
     */
    function bind(object|string $abstract): BindingBuilder
    {
        return app(BindingBuilder::class, ['abstract' => $abstract]);
    }
}

if (! function_exists('singleton')) {
    /**
     * @param  string  $abstract
     * @param  object|string|null  $concrete
     *
     * @return void
     */
    function singleton(string $abstract, object|string|null $concrete = null): void
    {
        app(BindingBuilder::class, ['abstract' => $abstract])->singleton($concrete);
    }
}

if (! function_exists('scoped')) {
    /**
     * @param  string  $abstract
     * @param  object|string|null  $concrete
     *
     * @return void
     */
    function scoped(string $abstract, object|string|null $concrete = null): void
    {
        app(BindingBuilder::class, ['abstract' => $abstract])->scoped($concrete);
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
