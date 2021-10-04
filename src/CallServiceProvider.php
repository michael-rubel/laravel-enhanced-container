<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer;

use MichaelRubel\EnhancedContainer\Core\CallProxy;
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
}
