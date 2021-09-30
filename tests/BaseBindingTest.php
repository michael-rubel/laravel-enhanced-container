<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructor;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructorClass;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithVariadicConstructor;

class BaseBindingTest extends TestCase
{
    /** @test */
    public function testCanBindAnAbstractToConcrete()
    {
        bind(BoilerplateInterface::class)->to(BoilerplateService::class);

        app()->bound(BoilerplateInterface::class);

        $instance = resolve(BoilerplateInterface::class);

        $this->assertInstanceOf(BoilerplateService::class, $instance);
    }

    /** @test */
    public function testCanBindAnAbstractToConcreteAsSingleton()
    {
        bind(BoilerplateInterface::class)->to(BoilerplateService::class, true);

        app()->bound(BoilerplateInterface::class);

        $instance = resolve(BoilerplateInterface::class);

        $this->assertInstanceOf(BoilerplateService::class, $instance);
    }

    /** @test */
    public function testCanBindAnAbstractToConcreteAsSingletonWithAnotherSyntax()
    {
        bind(BoilerplateInterface::class)->singleton(BoilerplateService::class);

        app()->bound(BoilerplateInterface::class);

        $instance = resolve(BoilerplateInterface::class);

        $this->assertInstanceOf(BoilerplateService::class, $instance);
    }

    /** @test */
    public function testCanBindAnAbstractToConcreteAsScopedInstance()
    {
        bind(BoilerplateInterface::class)->scoped(BoilerplateService::class);

        app()->bound(BoilerplateInterface::class);

        $instance = resolve(BoilerplateInterface::class);

        $this->assertInstanceOf(BoilerplateService::class, $instance);
    }

    /** @test */
    public function testCanUseContextualBindingWithNewSyntax()
    {
        bind(BoilerplateInterface::class)
            ->to(BoilerplateService::class)
            ->when(BoilerplateServiceWithConstructorClass::class);

        $test = resolve(
            BoilerplateServiceWithConstructorClass::class
        )->test();

        $this->assertInstanceOf(BoilerplateService::class, $test);
    }

    /** @test */
    public function testCanUseContextualBindingWithVariadicDependencies()
    {
        bind(BoilerplateInterface::class)
            ->to(function ($app) {
                return [
                    $app->make(BoilerplateService::class),
                    $app->make(BoilerplateServiceWithConstructor::class, ['param' => true]),
                ];
            })
            ->when(BoilerplateServiceWithVariadicConstructor::class);

        $test = resolve(
            BoilerplateServiceWithVariadicConstructor::class
        )->test();

        $this->assertIsArray($test);
        $this->assertInstanceOf(BoilerplateService::class, $test[0]);
        $this->assertInstanceOf(BoilerplateServiceWithConstructor::class, $test[1]);
    }
}
