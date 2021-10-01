<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructor;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructorClass;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructorPrimitive;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithVariadicConstructor;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithWrongContext;

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
            ->contextual(BoilerplateService::class)
            ->for(BoilerplateServiceWithConstructorClass::class);

        $test = call(
            BoilerplateServiceWithConstructorClass::class
        )->test();

        $this->assertInstanceOf(BoilerplateService::class, $test);
    }

    /** @test */
    public function testCanUseContextualBindingWithVariadicDependencies()
    {
        bind(BoilerplateInterface::class)
            ->contextual(
                fn ($app) => [
                    $app->make(BoilerplateService::class),
                    $app->make(BoilerplateServiceWithConstructor::class, ['param' => true]),
                ]
            )
            ->for(BoilerplateServiceWithVariadicConstructor::class);

        $test = call(
            BoilerplateServiceWithVariadicConstructor::class
        )->test();

        $this->assertIsArray($test);
        $this->assertInstanceOf(BoilerplateService::class, $test[0]);
        $this->assertInstanceOf(BoilerplateServiceWithConstructor::class, $test[1]);
    }

    /** @test */
    public function testContextualBindingServiceWithWrongContext()
    {
        bind(BoilerplateInterface::class)
            ->contextual(BoilerplateService::class)
            ->for(BoilerplateServiceWithConstructorClass::class);

        $test = call(
            BoilerplateServiceWithConstructorClass::class
        )->test();

        $this->assertInstanceOf(BoilerplateService::class, $test);

        // next call fails during to wrong instantiation context
        $this->expectException(\BadMethodCallException::class);

        call(BoilerplateServiceWithWrongContext::class);
    }

    /** @test */
    public function testCanUseMultipleContextualBindings()
    {
        bind(BoilerplateInterface::class)
            ->contextual(BoilerplateService::class)
            ->for(BoilerplateServiceWithConstructorClass::class);

        $test = call(
            BoilerplateServiceWithConstructorClass::class
        )->test();

        $this->assertInstanceOf(BoilerplateService::class, $test);

        bind(BoilerplateInterface::class)
            ->contextual(function ($app) {
                return [
                    $app->make(BoilerplateService::class),
                    $app->make(BoilerplateServiceWithConstructor::class, ['param' => true]),
                ];
            })
            ->for(BoilerplateServiceWithVariadicConstructor::class);

        $test = call(
            BoilerplateServiceWithVariadicConstructor::class
        )->test();

        $this->assertIsArray($test);
        $this->assertInstanceOf(BoilerplateService::class, $test[0]);
        $this->assertInstanceOf(BoilerplateServiceWithConstructor::class, $test[1]);

        // next call fails during to wrong instantiation context
        $this->expectException(\BadMethodCallException::class);

        call(BoilerplateServiceWithWrongContext::class);
    }

    /** @test */
    public function testCanContextualBindPrimitive()
    {
        bind('$param')
            ->contextual(false)
            ->for(BoilerplateServiceWithConstructor::class);

        $test = call(
            BoilerplateServiceWithConstructor::class
        )->getParam();

        $this->assertFalse($test);

        bind('$param')
            ->contextual(true)
            ->for(BoilerplateServiceWithConstructorPrimitive::class);

        $test = call(
            BoilerplateServiceWithConstructorPrimitive::class
        )->getParam();

        $this->assertTrue($test);
    }
}
