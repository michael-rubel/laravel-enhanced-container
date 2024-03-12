<?php

declare(strict_types=1);

use MichaelRubel\EnhancedContainer\Core\CallProxy;
use MichaelRubel\EnhancedContainer\Core\MethodBinder;

if (! function_exists('call')) {
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

    function bind(object|string $abstract): MethodBinder
    {
        return app(MethodBinder::class, ['abstract' => $abstract]);
    }
}
