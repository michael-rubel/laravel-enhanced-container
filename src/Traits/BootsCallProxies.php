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
     * @return void
     */
    public function bootCallProxies(): void
    {
        $dependencies = (
            new \ReflectionClass(static::class)
        )?->getConstructor()?->getParameters();

        if ($dependencies) {
            $this->proxy = new Fluent();

            collect($dependencies)->map(function ($param): void {
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
