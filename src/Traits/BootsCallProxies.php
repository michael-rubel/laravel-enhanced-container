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

        $method = is_null($method)
            ? $reflection->getConstructor()
            : $reflection->getMethod($method);

        $dependencies = $method->getParameters();

        if (! empty($dependencies)) {
            $this->proxy = new Fluent();

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
}
