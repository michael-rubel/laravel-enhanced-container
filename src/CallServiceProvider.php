<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall;

use MichaelRubel\ContainerCall\Concerns\MethodBinder;
use MichaelRubel\ContainerCall\Concerns\MethodBinding;
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
        $this->app->bind(Call::class, function ($_app, $service) {
            return new CallProxy(
                current($service)
            );
        });

        $this->app->bind(MethodBinding::class, function ($_app, $params) {
            $binder = new MethodBinder(
                current($params)
            );

            $binder->bind(
                next($params),
                last($params)
            );
        });
    }
}
