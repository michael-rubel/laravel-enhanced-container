<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;

class ExtendingTest extends TestCase
{
    /** @test */
    public function testCanExtendAbstractType()
    {
        bind(BoilerplateInterface::class)->to(BoilerplateService::class);
        bind(BoilerplateInterface::class)->extend(function($service) {
            $this->assertTrue($service->testProperty);

            $service->testProperty = false;

            return $service;
        });

        $this->assertFalse(
            call(BoilerplateInterface::class)->testProperty
        );
    }

    /** @test */
    public function testCanExtendAbstractTypeUsingHelper()
    {
        bind(BoilerplateInterface::class)->to(BoilerplateService::class);

        extend(BoilerplateInterface::class, function($service) {
            $this->assertTrue($service->testProperty);

            $service->testProperty = false;

            return $service;
        });

        $this->assertFalse(
            call(BoilerplateInterface::class)->testProperty
        );
    }
}
