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
     * Boots the collection of call proxies.
     */
    public function bootCallProxies(): void
    {
        $this->proxy = new Fluent();

        $dependencies = (
            new \ReflectionClass(static::class)
        )?->getConstructor()?->getParameters();

        if ($dependencies) {
            collect($dependencies)->map(function ($param): void {
                $class = $param->getType()->getName();

                if (class_exists($class) || interface_exists($class)) {
                    $this
                        ->proxy
                        ->{$param->getName()} = call($class);
                }
            });
        }
    }
}
