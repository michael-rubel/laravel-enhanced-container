<?php

namespace MichaelRubel\ContainerCall\Tests;

use MichaelRubel\ContainerCall\Tests\Boilerplate\BoilerplateService;

class MethodBindingTest extends TestCase
{
    /** @test */
    public function testCanOverrideMethodAsString()
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
    public function testCanOverrideMethodAsObject()
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

    /** @test */
    public function testCanOverrideMethodWithParameters()
    {
        bindMethod(
            BoilerplateService::class,
            'yourMethod',
            fn ($service, $app, $params) => $service->yourMethod($params['count']) + 1
        );

        $call = call(BoilerplateService::class)->yourMethod(100);

        $this->assertEquals(
            101,
            $call
        );
    }
}
