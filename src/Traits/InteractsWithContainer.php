<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Traits;

use Illuminate\Support\Arr;

trait InteractsWithContainer
{
    /**
     * Get the class instance.
     */
    protected function getInstance(string|object $class, array $dependencies = [], ?string $context = null): object
    {
        if (is_object($class)) {
            return $class;
        }

        $class        = $this->getClassForResolution($class, $context);
        $dependencies = $this->getDependencies($class, $dependencies);

        return app($class, $dependencies);
    }

    /**
     * Get the class for resolution.
     */
    protected function getClassForResolution(string $class, ?string $context = null): string
    {
        return isset(app()->contextual[$context])
            ? $this->getContextualConcrete($class, $context)
            : $class;
    }

    /**
     * Try to get the contextual concrete.
     */
    protected function getContextualConcrete(string $class, ?string $context = null): string
    {
        return app()->contextual[$context][$class] ?? $class;
    }

    /**
     * Try to get binding concrete.
     *
     *
     * @throws \ReflectionException
     */
    protected function getBindingConcrete(string $class): string
    {
        return (
           new \ReflectionFunction(
               app()->getBindings()[$class]['concrete']
           )
        )->getStaticVariables()['concrete'];
    }

    /**
     * Resolve class dependencies.
     *
     *
     * @throws \ReflectionException
     */
    protected function getDependencies(string $class, array $dependencies = []): array
    {
        if (! Arr::isAssoc($dependencies)) {
            if (! class_exists($class) && ! interface_exists($class)) {
                $class = $this->getBindingConcrete($class);
            }

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
     * @throws \ReflectionException
     */
    protected function getParameters(object $class, string $method, array $parameters): array
    {
        if (empty($parameters) || Arr::isAssoc($parameters)) {
            return $parameters;
        }

        return $this->makeContainerParameters(
            (new \ReflectionMethod($class, $method))->getParameters(),
            $parameters
        );
    }

    /**
     * Combine parameters to make it container-readable.
     */
    protected function makeContainerParameters(array $reflectionParameters, array $methodParameters): array
    {
        return collect($this->sliceParameters($reflectionParameters, $methodParameters))
            ->map->getName()
            ->combine($this->sliceParameters($methodParameters, $reflectionParameters))
            ->all();
    }

    /**
     * Slice an array to align the parameters.
     */
    protected function sliceParameters(array $parameters, array $countable): array
    {
        return array_slice($parameters, 0, count($countable));
    }

    /**
     * Convert the object to its namespace.
     */
    protected function convertToNamespace(object|string $object): string
    {
        return is_string($object) ? $object : $object::class;
    }
}
