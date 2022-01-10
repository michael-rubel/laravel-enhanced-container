<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Traits;

use Illuminate\Support\Fluent;

trait BootsCallProxies
{
    /**
     * @var Fluent|null
     */
    protected ?Fluent $proxy = null;

    /**
     * Boots the fluent object of call proxies.
     *
     * @param string|null $method
     *
     * @return void
     */
    public function bootCallProxies(?string $method = null): void
    {
        $reflection = new \ReflectionClass(static::class);

        $method = $this->getMethod($method, $reflection);

        $dependencies = $method?->getParameters();

        if (! empty($dependencies)) {
            $this->makeFluentObject();

            collect($dependencies)->map(function ($param) {
                $class = $param->getType()->getName();

                if (class_exists($class) || interface_exists($class)) {
                    property_exists(static::class, $param->getName()) && is_object($this->{$param->getName()})
                        ? $this->proxy->{$param->getName()} = call($this->{$param->getName()})
                        : $this->proxy->{$param->getName()} = call($class);
                }
            });
        }
    }

    /**
     * Determines which method to use.
     *
     * @param string|null      $method
     * @param \ReflectionClass $reflectionClass
     *
     * @return \ReflectionMethod|null
     */
    private function getMethod(?string $method, \ReflectionClass $reflectionClass): ?\ReflectionMethod
    {
        return is_null($method)
            ? $reflectionClass->getConstructor()
            : $reflectionClass->getMethod($method);
    }

    /**
     * Instantiate the Fluent object if it doesn't exist.
     *
     * @return void
     */
    private function makeFluentObject(): void
    {
        if (! $this->proxy instanceof Fluent) {
            $this->proxy = new Fluent();
        }
    }
}
