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

        if (class_exists($forwardsTo) || interface_exists($forwardsTo)) {
            return $this->resolvePassedClass($forwardsTo, $this->dependencies);
        }

        return null;
    }

    /**
     * Parse the class where to forward the call.
     *
     * @return string
     */
    public function forwardsTo(): string
    {
        return collect(
            $this->convertToNamespace($this->class)
        )->pipe(
            fn ($class) => collect(
                explode(self::CLASS_SEPARATOR, $class[0])
            )
        )->pipe(
            fn ($delimited) => $delimited->map(
                fn ($item) => str_replace(
                    Str::{config('enhanced-container.from.naming')}(config('enhanced-container.from.layer')),
                    Str::{config('enhanced-container.to.naming')}(config('enhanced-container.to.layer')),
                    $item
                )
            )
        )->pipe(
            fn ($structure) => implode(
                self::CLASS_SEPARATOR,
                $structure->put(
                    $structure->keys()->last(),
                    str_replace(
                        config('enhanced-container.from.layer'),
                        config('enhanced-container.to.layer'),
                        $structure->last() ?? ''
                    )
                )->all()
            )
        );
    }
}
