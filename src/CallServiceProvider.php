<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall;

use MichaelRubel\ContainerCall\Concerns\BindingBuilder;
use MichaelRubel\ContainerCall\Concerns\BindingBuilding;
use MichaelRubel\ContainerCall\Concerns\MethodForwarder;
use MichaelRubel\ContainerCall\Concerns\MethodForwarding;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CallServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package.
     *
     * @param Package $package
     *
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-container-calls')
            ->hasConfigFile();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function packageBooted(): void
    {
        $this->app->bind(Call::class, function ($_app, $params) {
            return new CallProxy(
                current($params),
                last($params)
            );
        });

        $this->app->bind(BindingBuilding::class, function ($_app, $class) {
            return new BindingBuilder(
                current($class)
            );
        });

        $this->app->bind(MethodForwarding::class, function ($_app, $params) {
            $forwarder = new MethodForwarder(
                current($params),
                last($params)
            );

            return $forwarder->forward();
        });
    }
}
