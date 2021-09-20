<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;

class CallProxy implements Call
{
    use HelpsProxies;

    /**
     * @var object|null
     */
    public ?object $resolvedInstance = null;

    /**
     * @var object|null
     */
    public ?object $resolvedInstanceForwardsTo = null;

    /**
     * CallProxy constructor.
     *
     * @param object|string $class
     * @param array         $dependencies
     *
     * @throws \ReflectionException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(
        private object | string $class,
        private array $dependencies = []
    ) {
        if (is_null($this->resolvedInstance)) {
            $this->resolvedInstance = $this->resolvePassedClass(
                $this->class,
                $this->dependencies
            );
        }

        if (config('enhanced-container.forwarding_enabled')) {
            if (is_null($this->resolvedInstanceForwardsTo)) {
                $this->resolvedInstanceForwardsTo = (
                    new MethodForwarder($this->class, $this->dependencies)
                )->resolveClass();
            }
        }
    }

    /**
     * Perform the container call.
     *
     * @param object $service
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function containerCall(object $service, string $method, array $parameters): mixed
    {
        return app()->call(
            [$service, $method],
            $this->getPassedParameters(
                $service,
                $method,
                $parameters
            )
        );
    }

    /**
     * Pass the call through container.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function __call(string $method, array $parameters): mixed
    {
        $call = function () use ($method, $parameters) {
            return $this->containerCall($this->resolvedInstance, $method, $parameters);
        };

        return rescue(
            fn () => $call(),
            function ($e) use ($method, $parameters) {
                if (config('enhanced-container.forwarding_enabled')) {
                    return $this->containerCall($this->resolvedInstanceForwardsTo, $method, $parameters);
                }

                throw new \BadMethodCallException($e->getMessage());
            }
        );
    }

    /**
     * Get the resolved service's property.
     *
     * @param string $name
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __get(string $name): mixed
    {
        return rescue(
            fn () => $this->resolvedInstance->{$name},
            function ($e) use ($name) {
                if (config('enhanced-container.forwarding_enabled')) {
                    return $this->resolvedInstanceForwardsTo->{$name};
                }

                throw new \BadMethodCallException($e->getMessage());
            }
        );
    }

    /**
     * Set the resolved service's property.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, mixed $value): void
    {
        rescue(
            fn () => $this->resolvedInstance->{$name} = $value,
            function ($e) use ($name, $value) {
                if (config('enhanced-container.forwarding_enabled')) {
                    return $this->resolvedInstanceForwardsTo->{$name} = $value;
                }

                throw new \BadMethodCallException($e->getMessage());
            }
        );
    }
}
