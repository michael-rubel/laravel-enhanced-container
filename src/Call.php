<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer;

interface Call
{
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
    public function containerCall(object $service, string $method, array $parameters): mixed;

    /**
     * Determine if the method should be forwarded.
     *
     * @param string $method
     *
     * @return bool
     */
    public function shouldForward(string $method): bool;
}
