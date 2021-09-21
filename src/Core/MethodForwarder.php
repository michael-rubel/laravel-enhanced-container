<?php

namespace MichaelRubel\EnhancedContainer\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;
use ReflectionException;

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
        if (is_object($this->class)) {
            $this->class = $this->class::class;
        }
    }

    /**
     * Forward the method.
     *
     * @return object
     * @throws ReflectionException|BindingResolutionException
     */
    public function getClass(): object
    {
        return $this->resolvePassedClass($this->forwardsTo(), $this->dependencies);
    }

    /**
     * Parse the class where to forward the call.
     *
     * @return string
     */
    public function forwardsTo(): string
    {
        return collect($this->class)
            ->pipe(
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
