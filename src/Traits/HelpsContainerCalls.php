<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall\Traits;

use ReflectionMethod;

trait HelpsContainerCalls
{
    /**
     * @param object|string $service
     * @param array         $dependencies
     *
     * @return object
     */
    public function resolvePassedService(object|string $service, array $dependencies = []): object
    {
        return is_object($service)
            ? $service
            : rescue(
                fn () => resolve($service, $dependencies),
                fn () => new $service(...$dependencies)
            );
    }

    /**
     * @param object       $service
     * @param string       $method
     * @param string|array $parameters
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getPassedParameters(object $service, string $method, string|array $parameters): array
    {
        if (empty($parameters)) {
            return [];
        }

        $reflectionMethod = new ReflectionMethod($service, $method);

        return collect(
            $reflectionMethod->getParameters()
        )->map(
            fn ($param) => $param->getName()
        )->combine($parameters)->all();
    }
}
