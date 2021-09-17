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
                ->pipe(fn ($class) => explode('\\', $class))
                ->pipe(fn ($delimited) => [
                    'class' => str_replace(
                        config('container-calls.from'),
                        config('container-calls.to'),
                        end($delimited)
                    ),
                    'folder' => '\\' . str_replace(
                        Str::plural(config('container-calls.from')),
                        Str::plural(config('container-calls.to')),
                        prev($delimited)
                    ),
                ])->get()
        );

        if ('\\' . Str::plural(config('container-calls.to')) === $path->get('folder')) {
            $path->put('folder', '');
        }

        return $this->resolveClass(
            $path->get('class'),
            $path->get('folder'),
            $this->dependencies
        );
    }

    /**
     * @param string $class
     * @param string $folder
     * @param array  $dependencies
     *
     * @return object
     */
    public function resolveClass(string $class, string $folder, array $dependencies): object
    {
        $app = config('container-calls.app');
        $to = config('container-calls.to');
        $to_plural = Str::plural($to);

        return rescue(
            fn () => resolve("$app\\$to_plural$folder\\$class", $dependencies)
        );
    }
}
