<?php

namespace MichaelRubel\EnhancedContainer\Core;

use Illuminate\Support\Collection;
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
        /** @var string */
        $naming_from = config('enhanced-container.from.naming') ?? 'pluralStudly';

        /** @var string */
        $naming_to = config('enhanced-container.to.naming') ?? 'pluralStudly';

        /** @var string */
        $layer_from = config('enhanced-container.from.layer') ?? 'Service';

        /** @var string */
        $layer_to = config('enhanced-container.to.layer') ?? 'Repository';

        return collect(
            $this->convertToNamespace($this->class)
        )->pipe(
            fn (Collection $class): Collection => collect(
                explode(self::CLASS_SEPARATOR, $class->first())
            )
        )->pipe(
            fn (Collection $delimited): Collection => $delimited->map(
                fn ($item) => str_replace(
                    Str::{$naming_from}($layer_from),
                    Str::{$naming_to}($layer_to),
                    $item
                )
            )
        )->pipe(
            fn (Collection $structure): string => implode(
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
