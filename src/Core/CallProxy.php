<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use BadMethodCallException;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Exceptions\PropertyNotFoundException;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;
use ReflectionException;

class CallProxy implements Call
{
    use HelpsProxies;

    /**
     * @var object
     */
    private object $resolved;

    /**
     * @var object
     */
    private object $resolvedForwardsTo;

    /**
     * CallProxy constructor.
     *
     * @param object|string $class
     * @param array         $dependencies
     */
    public function __construct(
        private object | string $class,
        private array $dependencies = []
    ) {
        $this->resolved = $this->resolvePassedClass(
            $this->class,
            $this->dependencies
        );

        if (config('enhanced-container.forwarding_enabled')) {
            $this->resolvedForwardsTo = (
                new MethodForwarder(
                    $this->class,
                    $this->dependencies
                )
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
        if (method_exists($this->resolved, $method)) {
            return $this->containerCall($this->resolved, $method, $parameters);
        } elseif (config('enhanced-container.forwarding_enabled')) {
            return $this->containerCall($this->resolvedForwardsTo, $method, $parameters);
        }

        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()',
            $this->resolved::class,
            $method
        ));
    }

    /**
     * Get the resolved service's property.
     *
     * @param string $name
     *
     * @return mixed
     * @throws PropertyNotFoundException
     */
    public function __get(string $name): mixed
    {
        if (property_exists($this->resolved, $name)) {
            return $this->resolved->{$name};
        } elseif (config('enhanced-container.forwarding_enabled')) {
            return $this->resolvedForwardsTo->{$name};
        }

        return $this->throwPropertyNotFoundException($name, $this->resolved);
    }

    /**
     * Set the resolved service's property.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws PropertyNotFoundException
     */
    public function __set(string $name, mixed $value): void
    {
        if (property_exists($this->resolved, $name)) {
            $this->resolved->{$name} = $value;
        } else {
            if (config('enhanced-container.forwarding_enabled')) {
                property_exists($this->resolvedForwardsTo, $name)
                    ? $this->resolvedForwardsTo->{$name} = $value
                    : $this->throwPropertyNotFoundException($name, $this->resolvedForwardsTo);

                return;
            }

            $this->throwPropertyNotFoundException($name, $this->resolved);
        }
    }
}
