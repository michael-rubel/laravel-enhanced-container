<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Traits\HelpsProxies;

class CallProxy implements Call
{
    use HelpsProxies;

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
            $service = $this->resolvePassedClass(
                $this->class,
                $this->dependencies
            );

            return $this->containerCall($service, $method, $parameters);
        };

        return rescue(
            fn () => $call(),
            function ($e) use ($method, $parameters) {
                if (config('enhanced-container.forwarding_enabled')) {
                    $forwarder = new MethodForwarder($this->class, $this->dependencies);

                    return $this->containerCall($forwarder->resolveClass(), $method, $parameters);
                }

                throw new \BadMethodCallException($e->getMessage());
            }
        );
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
}
