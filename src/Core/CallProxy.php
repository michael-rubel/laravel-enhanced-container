<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use BadMethodCallException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Traits\ForwardsCalls;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;
use ReflectionException;

class CallProxy implements Call
{
    use ForwardsCalls;
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
        if (method_exists($this->resolvedInstance, $method)) {
            return $this->containerCall($this->resolvedInstance, $method, $parameters);
        } elseif (config('enhanced-container.forwarding_enabled')) {
            return $this->containerCall($this->resolvedForwardingInstance, $method, $parameters);
        }

        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', $this->resolvedInstance::class, $method
        ));
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
        if (property_exists($this->resolvedInstance, $name)) {
            return $this->resolvedInstance->{$name};
        } elseif (config('enhanced-container.forwarding_enabled')) {
            return $this->resolvedForwardingInstance->{$name};
        }

        throw new \InvalidArgumentException(sprintf(
            'Call to undefined property %s::%s()', $this->resolvedInstance::class, $name
        ));
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
                    : $this->throwPropertyNotFound($name, $this->resolvedForwardingInstance);

                return;
            }

            $this->throwPropertyNotFound($name, $this->resolvedInstance);
        }
    }
}
