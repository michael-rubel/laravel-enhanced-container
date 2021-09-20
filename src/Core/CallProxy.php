<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;
use ReflectionException;

class CallProxy implements Call
{
    use HelpsProxies;

    /**
     * @var object
     */
    private object $resolvedInstance;

    /**
     * @var object
     */
    private object $resolvedForwardingInstance;

    /**
     * CallProxy constructor.
     *
     * @param object|string $class
     * @param array         $dependencies
     *
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public function __construct(
        private object | string $class,
        private array $dependencies = []
    ) {
        $this->resolvedInstance = $this->resolvePassedClass(
            $this->class,
            $this->dependencies
        );

        if (config('enhanced-container.forwarding_enabled')) {
            $this->resolvedForwardingInstance = (
                new MethodForwarder($this->class, $this->dependencies)
            )->getClass();
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
     * @throws ReflectionException
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
     * @throws ReflectionException
     */
    public function __call(string $method, array $parameters): mixed
    {
        return rescue(
            fn () => $this->containerCall($this->resolvedInstance, $method, $parameters),
            function ($e) use ($method, $parameters) {
                if (config('enhanced-container.forwarding_enabled')) {
                    return $this->containerCall($this->resolvedForwardingInstance, $method, $parameters);
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
     */
    public function __get(string $name): mixed
    {
        return rescue(
            fn () => $this->resolvedInstance->{$name},
            function ($e) use ($name) {
                if (config('enhanced-container.forwarding_enabled')) {
                    return $this->resolvedForwardingInstance->{$name};
                }

                throw new \InvalidArgumentException($e->getMessage());
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
        if (property_exists($this->resolvedInstance, $name)) {
            $this->resolvedInstance->{$name} = $value;
        } else {
            if (config('enhanced-container.forwarding_enabled')) {
                property_exists($this->resolvedForwardingInstance, $name)
                    ? $this->resolvedForwardingInstance->{$name} = $value
                    : $this->throwPropertyNotFoundException(
                        $name,
                        $this->resolvedForwardingInstance
                    );

                return;
            }

            $this->throwPropertyNotFoundException($name, $this->resolvedInstance);
        }
    }
}
