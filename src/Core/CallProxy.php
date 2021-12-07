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
    private object $instance;

    /**
     * @var object|null
     */
    private ?object $forwardsTo;

    /**
     * CallProxy constructor.
     *
     * @param object|string $class
     * @param array         $dependencies
     * @param string|null   $context
     */
    public function __construct(
        private object | string $class,
        private array $dependencies = [],
        private ?string $context = null
    ) {
        $this->instance = ! is_object($class)
            ? $this->resolvePassedClass(
                $this->class,
                $this->dependencies,
                $this->context
            )
            : $class;

        if (isForwardingEnabled()) {
            $this->forwardsTo = app(MethodForwarder::class, [
                'class'        => $this->class,
                'dependencies' => $this->dependencies,
            ])->getClass();
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
     * Gets the internal property by name.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function getInternal(string $property): mixed
    {
        return $this->{$property};
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
        if (method_exists($this->instance, $method)) {
            return $this->containerCall($this->instance, $method, $parameters);
        } elseif (isForwardingEnabled() && ! is_null($this->forwardsTo)) {
            return $this->containerCall($this->forwardsTo, $method, $parameters);
        }

        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()',
            $this->instance::class,
            $method
        ));
    }

    /**
     * Get the instance's property.
     *
     * @param string $name
     *
     * @return mixed
     * @throws PropertyNotFoundException
     */
    public function __get(string $name): mixed
    {
        if (property_exists($this->instance, $name)) {
            return $this->instance->{$name};
        } elseif (isForwardingEnabled() && ! is_null($this->forwardsTo)) {
            return $this->forwardsTo->{$name};
        }

        return $this->throwPropertyNotFoundException($name, $this->instance);
    }

    /**
     * Set the instance's property.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws PropertyNotFoundException
     */
    public function __set(string $name, mixed $value): void
    {
        if (property_exists($this->instance, $name)) {
            $this->instance->{$name} = $value;
        } else {
            if (isForwardingEnabled() && ! is_null($this->forwardsTo)) {
                property_exists($this->forwardsTo, $name)
                    ? $this->forwardsTo->{$name} = $value
                    : $this->throwPropertyNotFoundException($name, $this->forwardsTo);

                return;
            }

            $this->throwPropertyNotFoundException($name, $this->instance);
        }
    }
}
