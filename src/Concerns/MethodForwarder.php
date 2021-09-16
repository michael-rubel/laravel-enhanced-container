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
        private object $class,
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
                ->pipe(fn ($class) => explode('\\', $class::class))
                ->pipe(fn ($delimited) => [
                    'class' => Str::replace(
                        config('container-calls.from'),
                        '',
                        end($delimited)
                    ),
                ])->get()
        );

        return $this->resolveClass(
            $path->get('class'),
            $this->dependencies
        );
    }

    /**
     * @param string $class
     * @param array  $dependencies
     *
     * @return object
     */
    public function resolveClass(string $class, array $dependencies): object
    {
        $app = config('container-calls.app');
        $to = config('container-calls.to');
        $folder = Str::plural($to);

        $interfaces = collect([
            'Interface',
            'Contract',
        ]);

        $resolved = $interfaces->map(
            fn ($interface) => rescue(
                fn () => resolve("$app\\$folder\\$class$to$interface", $dependencies)
            )
        )->whereNotNull()->first();

        if (! $resolved) {
            $created = "$app\\$folder\\$class$to";

            return new $created(...$dependencies);
        }

        return $resolved;
    }
}
