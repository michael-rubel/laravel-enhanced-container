<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall;

use MichaelRubel\ContainerCall\Concerns\MethodForwarding;
use MichaelRubel\ContainerCall\Traits\HelpsContainerCalls;

class CallProxy implements Call
{
    use HelpsContainerCalls;

    /**
     * CallProxy constructor.
     *
     * @param object|string $service
     * @param array         $dependencies
     */
    public function __construct(
        private object | string $service,
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
            $service = $this->resolvePassedService(
                $this->service,
                $this->dependencies
            );

            return $this->containerCall($service, $method, $parameters);
        };

        return rescue(
            fn () => $call(),
            function ($e) use ($method, $parameters) {
                if (config('container-calls.forwarding_enabled')) {
                    $service = resolve(
                        MethodForwarding::class,
                        [$this->service, $this->dependencies]
                    );

                    return $this->containerCall($service, $method, $parameters);
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
