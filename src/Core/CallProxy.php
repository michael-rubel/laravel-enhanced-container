<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\ForwardsCalls;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;

class CallProxy implements Call
{
    use HelpsProxies, Conditionable, ForwardsCalls;

    /**
     * @var object
     */
    private object $instance;

    /**
     * CallProxy constructor.
     *
     * @param  object|string  $class
     * @param  array  $dependencies
     * @param  string|null  $context
     */
    public function __construct(
        object | string $class,
        array $dependencies = [],
        ?string $context = null
    ) {
        $this->instance = ! is_object($class)
            ? $this->resolvePassedClass($class, $dependencies, $context)
            : $class;
    }

    /**
     * Perform the container call.
     *
     * @param  object  $service
     * @param  string  $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function containerCall(object $service, string $method, array $parameters): mixed
    {
        try {
            return app()->call(
                [$service, $method],
                $this->getPassedParameters(
                    $service,
                    $method,
                    $parameters
                )
            );
        } catch (\ReflectionException) {
            return $this->forwardDecoratedCallTo($service, $method, $parameters);
        }
    }

    /**
     * Gets the internal property by name.
     *
     * @param  string  $property
     *
     * @return mixed
     */
    public function getInternal(string $property): mixed
    {
        return $this->{$property};
    }

    /**
     * @return void
     */
    protected function findClass(): void
    {
        $classes = app($this->instance::class . 'forwardsTo');

        collect($classes)->each(function ($class) {
            $instance = rescue(fn () => app($class));

            transform($instance, fn ($instance) => $this->instance = $instance);
        });
    }

    /**
     * Pass the call through container.
     *
     * @param  string  $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters): mixed
    {
        try {
            return $this->containerCall($this->instance, $method, $parameters);
        } catch (\Error $e) {
            if (Str::contains($e->getMessage(), 'Call to undefined method')) {
                $this->findClass();

                return $this->containerCall($this->instance, $method, $parameters);
            }

            throw $e;
        }
    }

    /**
     * Get the instance's property.
     *
     * @param  string  $name
     *
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (! property_exists($this->instance, $name)) {
            $this->findClass();
        }

        return $this->instance->{$name};
    }

    /**
     * Set the instance's property.
     *
     * @param  string  $name
     * @param  mixed  $value
     */
    public function __set(string $name, mixed $value): void
    {
        if (! property_exists($this->instance, $name)) {
            $this->findClass();
        }

        $this->instance->{$name} = $value;
    }
}
