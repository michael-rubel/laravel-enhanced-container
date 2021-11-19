<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer;

use Illuminate\Foundation\Application;
use MichaelRubel\EnhancedContainer\Core\CallProxy;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LecServiceProvider extends PackageServiceProvider
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
        $this->app->bind(Call::class, function (Application $app, array $params) {
            return new CallProxy(
                current($params),
                next($params),
                end($params)
            );
        });
    }
}
