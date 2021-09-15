<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall;

use MichaelRubel\ContainerCall\Traits\HelpsContainerCalls;

class CallProxy implements Call
{
    use HelpsContainerCalls;

    /**
     * CallProxy constructor.
     *
     * @param object|string $service
     * @param array         $parameters
     */
    public function __construct(
        private object | string $service,
        private array $parameters = []
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
            $this->parameters
        );

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
