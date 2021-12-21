<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Traits;

use Illuminate\Support\Arr;
use JetBrains\PhpStorm\Pure;
use MichaelRubel\EnhancedContainer\Exceptions\PropertyNotFoundException;

trait HelpsProxies
{
    /**
     * @param string      $class
     * @param array       $dependencies
     * @param string|null $context
     *
     * @return object
     */
    public function resolvePassedClass(string $class, array $dependencies = [], ?string $context = null): object
    {
        $class        = $this->getClassForResolution($class, $context);
        $dependencies = $this->getDependencies($class, $dependencies);

        return resolve($class, $dependencies);
    }

    /**
     * Get the class for resolution.
     *
     * @param string      $class
     * @param string|null $context
     *
     * @return string
     */
    public function getClassForResolution(string $class, ?string $context = null): string
    {
        return ! is_null($context) && isset(app()->contextual[$context])
            ? $this->getContextualConcrete($class, $context)
            : $class;
    }

    /**
     * Try to get the contextual concrete.
     *
     * @param string      $class
     * @param string|null $context
     *
     * @return string
     */
    public function getContextualConcrete(string $class, ?string $context = null): string
    {
        return app()->contextual[$context][$class] ?? $class;
    }

    /**
     * Resolve class dependencies.
     *
     * @param string $class
     * @param array  $dependencies
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getDependencies(string $class, array $dependencies = []): array
    {
        if (! empty($dependencies) && ! Arr::isAssoc($dependencies)) {
            /** @var class-string $class */
            $constructor = (new \ReflectionClass($class))->getConstructor();

            if ($constructor) {
                $dependencies = $this->makeContainerParameters(
                    $constructor->getParameters(),
                    $dependencies
                );
            }
        }

        return $dependencies;
    }

    /**
     * @param object $class
     * @param string $method
     * @param array  $parameters
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
     * @param array $reflectionParameters
     * @param array $methodParameters
     *
     * @return array
     */
    public function makeContainerParameters(array $reflectionParameters, array $methodParameters): array
    {
        $base = current($methodParameters);

        if ($this->isOrderable($base, $reflectionParameters, $methodParameters)) {
            return $base;
        }

        return collect($reflectionParameters)
            ->slice(0, count($methodParameters))
            ->map(fn ($param) => $param->getName())
            ->combine(array_slice($methodParameters, 0, count($reflectionParameters)))
            ->all();
    }

    /**
     * Determine if the container can handle parameter order.
     *
     * @param array $base
     * @param array $reflectionParameters
     * @param array $methodParameters
     *
     * @return bool
     */
    public function isOrderable(mixed $base, array $reflectionParameters, array $methodParameters): bool
    {
        return is_array($base) && Arr::isAssoc($base) && single($methodParameters) <=> single($reflectionParameters);
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
