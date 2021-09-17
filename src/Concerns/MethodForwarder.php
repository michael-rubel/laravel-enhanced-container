<?php

namespace MichaelRubel\ContainerCall\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MichaelRubel\ContainerCall\Traits\HelpsContainerCalls;

class MethodForwarder implements MethodForwarding
{
    use HelpsContainerCalls;

    /**
     * @param string $class
     * @param array  $dependencies
     */
    public function __construct(
        private string $class,
        private array $dependencies
    ) {
    }

    /**
     * Forward the method.
     *
     * @return object
     * @throws \ReflectionException
     */
    public function forward(): object
    {
        return rescue(
            fn () => $this->resolvePassedService($this->forwardsTo(), $this->dependencies),
            fn () => throw new \BadMethodCallException('Unable to forward the method. Check if your call is valid.')
        );
    }

    /**
     * Parse the class where to forward the call.
     *
     * @return string
     */
    public function forwardsTo(): string
    {
        return collect(
            take($this->class)
                ->pipe(fn ($class) => explode(self::CLASS_SEPARATOR, $class))
                ->pipe(
                    fn ($delimited) => collect($delimited)->map(
                        fn ($item) => str_replace(
                            Str::plural(config('container-calls.from')),
                            Str::plural(config('container-calls.to')),
                            $item
                        )
                    )
                )->pipe(
                    fn ($structure) => implode(
                        self::CLASS_SEPARATOR,
                        collect(
                            $structure->put(
                                $structure->keys()->last(),
                                str_replace(
                                    config('container-calls.from'),
                                    config('container-calls.to'),
                                    $structure->last() ?? ''
                                )
                            )
                        )->toArray()
                    )
                )->get()
        )->first();
    }
}
