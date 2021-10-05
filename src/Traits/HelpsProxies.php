<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Traits;

use Illuminate\Support\Arr;
use MichaelRubel\EnhancedContainer\Exceptions\PropertyNotFoundException;

trait HelpsProxies
{
    /**
     * @param object|string $class
     * @param array         $dependencies
     *
     * @return object
     */
    public function resolvePassedClass(object|string $class, array $dependencies = []): object
    {
        return is_object($class)
            ? $class
            : rescue(
                function () use ($class, $dependencies): object {
                    if (! empty($dependencies) && ! Arr::isAssoc($dependencies)) {
                        $constructor = (new \ReflectionClass($class))->getConstructor();

                        if ($constructor) {
                            $dependencies = $this->makeContainerParameters(
                                $constructor->getParameters(),
                                $dependencies
                            );
                        }
                    }

                    return resolve($class, $dependencies);
                },
                fn ($e) => throw new \BadMethodCallException($e->getMessage())
            );
    }

    /**
     * @param object       $class
     * @param string       $method
     * @param array $parameters
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getPassedParameters(object $class, string $method, array $parameters): array
    {
        if (empty($parameters)) {
            return $parameters;
        }

        $reflectionMethod = new \ReflectionMethod($class, $method);

        return $this->makeContainerParameters(
            $reflectionMethod->getParameters(),
            $parameters
        );
    }

    /**
     * Combine parameters to make it container-readable.
     *
     * @param array $parameters
     * @param array $toCombine
     *
     * @return array
     */
    public function makeContainerParameters(array $parameters, array $toCombine): array
    {
        return collect($parameters)
            ->slice(0, count($toCombine))
            ->map(fn ($param) => $param->getName())
            ->combine(array_slice($toCombine, 0, count($parameters)))
            ->all();
    }

    /**
     * Convert the object to its namespace.
     *
     * @param object|string $object
     *
     * @return string
     */
    public function convertToNamespace(object|string $object): string
    {
        return is_string($object)
            ? $object
            : $object::class;
    }

    /**
     * Handle property error.
     *
     * @param string $name
     * @param object $instance
     *
     * @throws PropertyNotFoundException
     */
    public function throwPropertyNotFoundException(string $name, object $instance): self
    {
        throw new PropertyNotFoundException(sprintf(
            'Call to undefined property %s::%s()',
            $name,
            $instance::class
        ));
    }
}
