<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use Illuminate\Container\BoundMethod;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Exceptions\InstanceInteractionException;
use MichaelRubel\EnhancedContainer\Traits\InteractsWithContainer;

class CallProxy implements Call
{
    use ForwardsCalls, InteractsWithContainer;

    /**
     * Current proxy instance.
     */
    protected object $instance;

    /**
     * Previous proxy instance.
     */
    protected ?object $previous = null;

    /**
     * Determines if the forwarding is enabled in this proxy.
     */
    protected bool $forwarding = true;

    /**
     * Saves proxy interactions (method calls, property assignments, etc).
     */
    protected array $interactions = [];

    /**
     * Initialize a new CallProxy.
     */
    public function __construct(object|string $class, array $dependencies = [], ?string $context = null)
    {
        $this->instance = $this->getInstance($class, $dependencies, $context);
    }

    /**
     * Gets the internal property by name.
     */
    public function getInternal(string $property): mixed
    {
        return $this->{$property};
    }

    /**
     * Sets the internal instance to previous one.
     */
    public function setPrevious(): static
    {
        if ($this->previous) {
            $oldInstance = $this->instance;

            $this->instance = $this->previous;

            $this->previous = $oldInstance;
        }

        return $this;
    }

    /**
     * Disables the forwarding on the proxy level.
     */
    public function disableForwarding(): static
    {
        $this->forwarding = false;

        return $this;
    }

    /**
     * Enables the forwarding on the proxy level.
     */
    public function enableForwarding(): static
    {
        $this->forwarding = true;

        return $this;
    }

    /**
     * Perform the container call.
     */
    protected function containerCall(object $service, string $method, array $parameters): mixed
    {
        try {
            return BoundMethod::call(
                Container::getInstance(), [$service, $method], $this->getParameters($service, $method, $parameters)
            );
        } catch (\ReflectionException) {
            return $this->forwardCallTo($service, $method, $parameters);
        }
    }

    /**
     * Find the forwarding instance if bound.
     */
    protected function findForwardingInstance(): void
    {
        $clue = $this->instance::class . Forwarding::CONTAINER_KEY;

        if ($this->forwarding && app()->bound($clue)) {
            $newInstance = app($clue);

            $this->previous = $this->instance;
            $this->instance = $newInstance;
        }
    }

    /**
     * Save the interaction with proxy.
     */
    protected function interact(string $name, string $type): void
    {
        $this->interactions[$name] = $type;
    }

    /**
     * Check the proxy has previous interaction
     * with the same method or property.
     */
    protected function hasPreviousInteraction(string $name): bool
    {
        return $this->previous && isset($this->interactions[$name]);
    }

    /**
     * Handle the missing by error message.
     */
    protected function handleMissing(\Closure $callback, string $by): mixed
    {
        try {
            return $callback();
        } catch (\Error|\ErrorException $e) {
            if (Str::contains($e->getMessage(), $by)) {
                $this->findForwardingInstance();

                return $callback();
            }

            throw $e;
        }
    }

    /**
     * Pass the call through container.
     */
    public function __call(string $method, array $parameters): mixed
    {
        if (! method_exists($this->instance, $method)) {
            if ($this->hasPreviousInteraction($method)) {
                throw new InstanceInteractionException;
            }

            $this->findForwardingInstance();
        }

        $this->interact($method, Call::METHOD);

        return $this->handleMissing(
            fn () => $this->containerCall($this->instance, $method, $parameters),
            by: 'Call to undefined method'
        );
    }

    /**
     * Get the instance's property.
     */
    public function __get(string $name): mixed
    {
        if (! property_exists($this->instance, $name)) {
            if ($this->hasPreviousInteraction($name)) {
                throw new InstanceInteractionException;
            }

            $this->findForwardingInstance();
        }

        $this->interact($name, Call::GET);

        return $this->handleMissing(
            fn () => $this->instance->{$name},
            by: 'Undefined property'
        );
    }

    /**
     * Set the instance's property.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->interact($name, Call::SET);

        $this->instance->{$name} = $value;
    }

    /**
     * Check the property is set.
     */
    public function __isset(string $name): bool
    {
        $this->interact($name, Call::ISSET);

        return isset($this->instance->{$name});
    }

    /**
     * Unset the property.
     */
    public function __unset(string $name): void
    {
        $this->interact($name, Call::UNSET);

        unset($this->instance->{$name});
    }
}
