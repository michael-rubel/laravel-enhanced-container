<?php

namespace MichaelRubel\ContainerCall\Tests;

use MichaelRubel\ContainerCall\Tests\Boilerplate\BoilerplateService;

class MethodBindingTest extends TestCase
{
    /** @test */
    public function testCanProperlyOverrideMethodAsString()
    {
        bindMethod(
            BoilerplateService::class,
            'test',
            fn () => 'overridden'
        );

        $call = call(BoilerplateService::class)->test('test', 1);

        $this->assertEquals('overridden', $call);
    }

    /** @test */
    public function testCanProperlyOverrideMethodAsObject()
    {
        bindMethod(
            new BoilerplateService(),
            'test',
            fn () => collect('illuminate')
        );

        $call = call(BoilerplateService::class)->test('test', 1);

        $this->assertEquals(
            collect('illuminate'),
            $call
        );
    }

    /** @test */
    public function testCanOverrideMethodUsingService()
    {
        bindMethod(
            new BoilerplateService(),
            'yourMethod',
            fn ($service, $app) => $service->yourMethod(100) + 1
        );

        $call = call(BoilerplateService::class)->yourMethod(100);

        $this->assertEquals(
            101,
            $call
        );
    }
}
