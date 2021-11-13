<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructor;

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
    public function testCanSetInstanceToTheContainer()
    {
        bind(BoilerplateInterface::class)->to(BoilerplateService::class);
        bind(BoilerplateInterface::class)->instance(new BoilerplateServiceWithConstructor(true));

        $this->assertInstanceOf(
            BoilerplateServiceWithConstructor::class,
            resolve(BoilerplateInterface::class)
        );
    }
}
