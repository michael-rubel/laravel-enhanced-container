<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall;

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
}
