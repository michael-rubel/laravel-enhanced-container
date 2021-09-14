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
     */
    public function __construct(
        private object | string $service
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
        $service = $this->resolvePassedService($this->service);

        return app()->call(
            $service::class,
            $this->getPassedParameters(
                $service,
                $method,
                $parameters
            ),
            $method
        );
    }
}
