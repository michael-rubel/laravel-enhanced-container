<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall\Traits;

use Illuminate\Support\Arr;

trait HelpsProxies
{
    /**
     * @param object|string $class
     * @param array         $dependencies
     *
     * @return object
     * @throws \ReflectionException
     */
    public function resolvePassedClass(object|string $class, array $dependencies = []): object
    {
        return is_object($class)
            ? $class
            : rescue(
                function () use ($class, $dependencies): mixed {
                    if (! empty($dependencies) && ! Arr::isAssoc($dependencies)) {
                        $constructor = (new \ReflectionClass($class))->getConstructor();

                        if ($constructor) {
                            $dependencies = collect($constructor->getParameters())->map(
                                fn ($parameter) => $parameter->getName()
                            )->combine($dependencies)->all();
                        }
                    }

                    return resolve($class, $dependencies);
                },
                fn ($e) => throw new \BadMethodCallException($e->getMessage()),
                false
            );
    }

    /**
     * @param object       $class
     * @param string       $method
     * @param string|array $parameters
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getPassedParameters(object $class, string $method, string|array $parameters): array
    {
        if (empty($parameters)) {
            return [];
        }

        $reflectionMethod = new \ReflectionMethod($class, $method);

        return collect(
            $reflectionMethod->getParameters()
        )->map(
            fn ($param) => $param->getName()
        )->combine($parameters)->all();
    }

    /**
     * Examine if it is object or not.
     *
     * @param string|object $class
     *
     * @return string|object
     */
    public function getClassToBaseBinding(string|object $class): string|object
    {
        return is_object($class)
            ? $class::class
            : $class;
    }
}
