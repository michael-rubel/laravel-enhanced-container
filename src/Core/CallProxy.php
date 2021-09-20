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
    public ?object $resolvedService = null;

    /**
     * @var object|null
     */
    public ?object $resolvedServiceForwardsTo = null;

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
        if (is_null($this->resolvedService)) {
            $this->resolvedService = $this->resolvePassedClass(
                $this->class,
                $this->dependencies
            );
        }

        if (config('enhanced-container.forwarding_enabled')) {
            if (is_null($this->resolvedServiceForwardsTo)) {
                $this->resolvedServiceForwardsTo = (
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
            return $this->containerCall($this->resolvedService, $method, $parameters);
        };

        return rescue(
            fn () => $call(),
            function ($e) use ($method, $parameters) {
                if (config('enhanced-container.forwarding_enabled')) {
                    return $this->containerCall($this->resolvedServiceForwardsTo, $method, $parameters);
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
            fn () => $this->resolvedService->{$name},
            function ($e) use ($name) {
                if (config('enhanced-container.forwarding_enabled')) {
                    return $this->resolvedServiceForwardsTo->{$name};
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
            fn () => $this->resolvedService->{$name} = $value,
            function ($e) use ($name, $value) {
                if (config('enhanced-container.forwarding_enabled')) {
                    return $this->resolvedServiceForwardsTo->{$name} = $value;
                }

                throw new \BadMethodCallException($e->getMessage());
            }
        );
    }
}
