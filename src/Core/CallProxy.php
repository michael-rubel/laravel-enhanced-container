<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Exceptions\InstanceInteractionException;
use MichaelRubel\EnhancedContainer\Traits\InteractsWithContainer;

class CallProxy implements Call
{
    use InteractsWithContainer, ForwardsCalls;

    /**
     * @var object
     */
    protected object $instance;

    /**
     * @var string
     */
    protected string $previous;

    /**
     * @var array
     */
    protected array $interactions = [];

    /**
     * Initialize a new CallProxy.
     *
     * @param  object|string  $class
     * @param  array  $dependencies
     * @param  string|null  $context
     */
    public function __construct(object|string $class, array $dependencies = [], ?string $context = null)
    {
        $this->instance = $this->getInstance($class, $dependencies, $context);
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
                $this->getParameters($service, $method, $parameters)
            );
        } catch (\ReflectionException) {
            return $this->forwardCallTo($service, $method, $parameters);
        }
    }

    /**
     * Find the forwarding instance if bound.
     *
     * @return void
     */
    protected function findForwardingInstance(): void
    {
        $clue = $this->instance::class . Forwarding::CONTAINER_KEY;

        if (app()->bound($clue)) {
            $newInstance = rescue(fn () => app($clue), report: false);

            if (! is_null($newInstance)) {
                $this->previous = $this->instance::class;
                $this->instance = $newInstance;
            }
        }
    }

    /**
     * Save the interaction with proxy.
     *
     * @param  string  $name
     * @param  string  $type
     *
     * @return void
     */
    protected function interact(string $name, string $type): void
    {
        $this->interactions[$name] = $type;
    }

    /**
     * Check the proxy has previous interaction
     * with the same method or property.
     *
     * @param  string  $name
     *
     * @return bool
     */
    protected function hasPreviousInteraction(string $name): bool
    {
        return isset($this->interactions[$name]) && isset($this->previous);
    }

    /**
     * Handle the missing by error message.
     *
     * @param  \Closure  $callback
     * @param  string  $by
     *
     * @return mixed
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
     *
     * @param  string  $method
     * @param  array  $parameters
     *
     * @return mixed
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
     *
     * @param  string  $name
     *
     * @return mixed
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
     *
     * @param  string  $name
     * @param  mixed  $value
     */
    public function __set(string $name, mixed $value): void
    {
        $this->interact($name, Call::SET);

        $this->instance->{$name} = $value;
    }

    /**
     * Check the property is set.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->instance->{$name});
    }

    /**
     * Unset the property.
     *
     * @param  string  $name
     *
     * @return void
     */
    public function __unset(string $name): void
    {
        unset($this->instance->{$name});
    }
}
