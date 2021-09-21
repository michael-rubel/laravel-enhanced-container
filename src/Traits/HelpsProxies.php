<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use ReflectionException;

trait HelpsProxies
{
    /**
     * @param object|string $class
     * @param array         $dependencies
     *
     * @return object
     * @throws ReflectionException
     * @throws BindingResolutionException
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
                            $dependencies = $this->makeContainerParameters(
                                $constructor->getParameters(),
                                $dependencies
                            );
                        }
                    }

                    return app()->make($class, $dependencies);
                },
                fn ($e) => throw new \BadMethodCallException($e->getMessage()),
                false
            );
    }

    /**
     * @param object       $class
     * @param string       $method
     * @param array $parameters
     *
     * @return array
     * @throws ReflectionException
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
            ->map(
                fn ($param) => $param->getName()
            )->combine($toCombine)->all();
    }

    /**
     * Handle property error.
     *
     * @param string $name
     * @param object $instance
     */
    public function throwPropertyNotFound(string $name, object $instance): void
    {
        throw new \InvalidArgumentException(
            'Property '
            . $name
            . ' not found in class '
            . $instance::class
        );
    }
}
