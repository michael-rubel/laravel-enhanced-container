<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructor;

class HelpersTest extends TestCase
{
    /** @test */
    public function testCanSetBindItself()
    {
        bind(BoilerplateService::class)->itself();

        $boilerplateOne = resolve(BoilerplateService::class);
        $boilerplateTwo = resolve(BoilerplateService::class);

        $this->assertNotSame($boilerplateOne, $boilerplateTwo);
    }

    /** @test */
    public function testCanSetSingleton()
    {
        singleton(BoilerplateService::class);

        $boilerplateOne = resolve(BoilerplateService::class);
        $boilerplateTwo = resolve(BoilerplateService::class);

        $this->assertSame($boilerplateOne, $boilerplateTwo);
    }

    /** @test */
    public function testCanSetScopedInstance()
    {
        scoped(BoilerplateService::class);

        $boilerplateOne = resolve(BoilerplateService::class);
        $boilerplateTwo = resolve(BoilerplateService::class);

        $this->assertSame($boilerplateOne, $boilerplateTwo);
    }

    /** @test */
    public function testCanExtendAbstractTypeUsingHelper()
    {
        bind(BoilerplateInterface::class)->to(BoilerplateService::class);

        extend(BoilerplateInterface::class, function ($service) {
            $this->assertTrue($service->testProperty);

            $service->testProperty = false;

            return $service;
        });

        $this->assertFalse(
            call(BoilerplateInterface::class)->testProperty
        );
    }

    /** @test */
    public function testCanSetInstanceToTheContainerUsingHelper()
    {
        bind(BoilerplateInterface::class)->to(BoilerplateService::class);

        instance(BoilerplateInterface::class, new BoilerplateServiceWithConstructor(true));

        $this->assertInstanceOf(
            BoilerplateServiceWithConstructor::class,
            resolve(BoilerplateInterface::class)
        );
    }
}
