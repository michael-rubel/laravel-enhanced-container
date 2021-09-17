<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall\Traits;

use Illuminate\Support\Arr;

trait HelpsContainerCalls
{
    /**
     * @param object|string $service
     * @param array         $dependencies
     *
     * @return object
     * @throws \ReflectionException
     */
    public function resolvePassedService(object|string $service, array $dependencies = []): object
    {
        return is_object($service)
            ? $service
            : rescue(
                function () use ($service, $dependencies): mixed {
                    if (! empty($dependencies) && ! Arr::isAssoc($dependencies)) {
                        $constructor = (new \ReflectionClass($service))->getConstructor();

                        if ($constructor) {
                            $dependencies = collect($constructor->getParameters())->map(
                                fn ($parameter) => $parameter->getName()
                            )->combine($dependencies)->all();
                        }
                    }

                    return resolve($service, $dependencies);
                },
                fn ($e) => throw new \BadMethodCallException($e->getMessage()),
                false
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

        $reflectionMethod = new \ReflectionMethod($service, $method);

        return collect(
            $reflectionMethod->getParameters()
        )->map(
            fn ($param) => $param->getName()
        )->combine($parameters)->all();
    }
}
