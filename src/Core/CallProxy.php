<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use Illuminate\Support\Traits\ForwardsCalls;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Traits\InteractsWithContainer;

class CallProxy implements Call
{
    use InteractsWithContainer, ForwardsCalls;

    /**
     * @var object
     */
    protected object $instance;

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
        $this->instance = $this->resolvePassedClass($class, $dependencies, $context);
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
     * Perform the container call.
     *
     * @param  object  $service
     * @param  string  $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    protected function containerCall(object $service, string $method, array $parameters): mixed
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
            return $this->forwardCallTo($service, $method, $parameters);
        }
    }

    /**
     * @return void
     */
    protected function findForwardedClass(): void
    {
        $clue = $this->instance::class . Forwarding::CONTAINER_KEY;

        $instance = rescue(fn () => app($clue), report: false);

        transform($instance, fn () => $this->instance = $instance);
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
        if (! method_exists($this->instance, $method)) {
            $this->findForwardedClass();
        }

        return $this->containerCall($this->instance, $method, $parameters);
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
            $this->findForwardedClass();
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
            $this->findForwardedClass();
        }

        $this->instance->{$name} = $value;
    }
}
