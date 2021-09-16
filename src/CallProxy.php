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
        $service = $this->resolvePassedService(
            $this->service,
            $this->dependencies
        );

        $call = function () use ($service, $method, $parameters) {
            return app()->call(
                [$service, $method],
                $this->getPassedParameters(
                    $service,
                    $method,
                    $parameters
                )
            );
        };

        if (config('container-calls.forwarding_enabled')) {
            return rescue(
                fn () => $call(),
                fn () => app()->call(
                    [resolve(
                        MethodForwarding::class,
                        [$service, $this->dependencies]
                    ), $method],
                    $this->getPassedParameters(
                        $service,
                        $method,
                        $parameters
                    )
                )
            );
        }

        return $call();
    }
}
