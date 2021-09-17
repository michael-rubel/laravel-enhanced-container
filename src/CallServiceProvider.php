<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer;

use MichaelRubel\EnhancedContainer\Concerns\BindingBuilder;
use MichaelRubel\EnhancedContainer\Concerns\Binding;
use MichaelRubel\EnhancedContainer\Concerns\MethodForwarder;
use MichaelRubel\EnhancedContainer\Concerns\Forwarding;
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
            ->name('laravel-enhanced-container')
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

        $this->app->bind(Binding::class, function ($_app, $class) {
            return new BindingBuilder(
                current($class)
            );
        });

        $this->app->bind(Forwarding::class, function ($_app, $params) {
            $forwarder = new MethodForwarder(
                current($params),
                last($params)
            );

            return $forwarder->forward();
        });
    }
}
