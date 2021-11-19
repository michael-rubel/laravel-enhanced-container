<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceResolvesContextualInMethod;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceResolvesGlobalInMethod;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructor;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructorClass;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructorPrimitive;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithVariadicConstructor;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithWrongContext;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\TestService;

class ContextualBindingTest extends TestCase
{
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

    /** @test */
    public function testCallProxyResolvesContextualBinding()
    {
        // bind the service globally
        bind(BoilerplateInterface::class)
            ->singleton(BoilerplateService::class);

        $call = call(BoilerplateInterface::class);

        $this->assertInstanceOf(
            BoilerplateService::class,
            $call->getInternal('instance')
        );

        // set contextual
        bind(BoilerplateInterface::class)
            ->contextual(TestService::class)
            ->for(BoilerplateServiceResolvesContextualInMethod::class);

        $service = call(BoilerplateServiceResolvesContextualInMethod::class);

        $this->assertInstanceOf(
            TestService::class,
            $service->constructorHasContextual()
        );

        $this->assertInstanceOf(
            TestService::class,
            $service->methodHasContextual()->getInternal('instance')
        );

        $this->assertInstanceOf(
            TestService::class,
            $service->methodHasContextual2()->getInternal('instance')
        );

        $this->assertInstanceOf(
            BoilerplateService::class,
            $service->methodHasGlobal()->getInternal('instance')
        );

        // ensure global still available for other classes
        $service = call(BoilerplateServiceResolvesGlobalInMethod::class);

        $this->assertInstanceOf(
            BoilerplateService::class,
            $service->getsGlobalBinding()->getInternal('instance')
        );
    }
}
