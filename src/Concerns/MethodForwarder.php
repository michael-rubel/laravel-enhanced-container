<?php

namespace MichaelRubel\ContainerCall\Concerns;

use Illuminate\Support\Str;

class MethodForwarder implements MethodForwarding
{
    /**
     * @param object $class
     * @param array  $dependencies
     */
    public function __construct(
        private object | string $class,
        private array $dependencies
    ) {
    }

    /**
     * Forward the method.
     *
     * @return object
     */
    public function forward(): object
    {
        $path = collect(
            take($this->class)
                ->pipe(fn ($class) => explode(self::CLASS_SEPARATOR, $class))
                ->pipe(function ($delimited) {
                    $structure = collect($delimited)->map(
                        fn ($item) => str_replace(
                            Str::plural(config('container-calls.from')),
                            Str::plural(config('container-calls.to')),
                            $item
                        )
                    );

                    $last = str_replace(
                        config('container-calls.from'),
                        config('container-calls.to'),
                        $structure->last() ?? ''
                    );

                    return implode(
                        self::CLASS_SEPARATOR,
                        collect(
                            $structure->put($structure->keys()->last(), $last)
                        )->toArray()
                    );
                })->get()
        )->first();

        return rescue(
            fn () => resolve($path, $this->dependencies)
        );
    }
}
