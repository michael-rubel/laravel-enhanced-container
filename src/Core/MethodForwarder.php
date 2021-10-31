<?php

namespace MichaelRubel\EnhancedContainer\Core;

use Illuminate\Support\Str;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;

class MethodForwarder
{
    use HelpsProxies;

    /**
     * @const CLASS_SEPARATOR
     */
    public const CLASS_SEPARATOR = '\\';

    /**
     * @param object|string $class
     * @param array         $dependencies
     */
    public function __construct(
        private object | string $class,
        private array $dependencies
    ) {
    }

    /**
     * Forward the method.
     *
     * @return object|null
     */
    public function getClass(): ?object
    {
        $forwardsTo = $this->forwardsTo();

        return class_exists($forwardsTo) || interface_exists($forwardsTo)
            ? $this->resolvePassedClass($forwardsTo, $this->dependencies)
            : null;
    }

    /**
     * Parse the class where to forward the call.
     *
     * @return string
     */
    public function forwardsTo(): string
    {
        $naming_from = config('enhanced-container.from.naming') ?? 'pluralStudly';
        $naming_to   = config('enhanced-container.to.naming') ?? 'pluralStudly';
        $layer_from  = config('enhanced-container.from.layer') ?? 'Service';
        $layer_to    = config('enhanced-container.to.layer') ?? 'Repository';

        return collect(
            $this->convertToNamespace($this->class)
        )->pipe(
            fn ($class) => collect(
                explode(self::CLASS_SEPARATOR, $class[0])
            )
        )->pipe(
            fn ($delimited) => $delimited->map(
                fn ($item) => str_replace(
                    Str::{$naming_from}($layer_from),
                    Str::{$naming_to}($layer_to),
                    $item
                )
            )
        )->pipe(
            fn ($structure) => implode(
                self::CLASS_SEPARATOR,
                $structure->put(
                    $structure->keys()->last(),
                    str_replace(
                        $layer_from,
                        $layer_to,
                        $structure->last() ?? ''
                    )
                )->all()
            )
        );
    }
}
