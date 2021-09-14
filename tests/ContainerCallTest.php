<?php

namespace MichaelRubel\ContainerCall\Tests;

use MichaelRubel\ContainerCall\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\ContainerCall\Tests\Boilerplate\BoilerplateService;

class ContainerCallTest extends TestCase
{
    /** @test */
    public function testCanProperlyCallTheServiceAsString()
    {
        $test = call(BoilerplateService::class)->test('test', 1);

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanProperlyCallTheServiceAsObject()
    {
        $test = call(new BoilerplateService())->test('test', 1);

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanProperlyCallTheServiceUsingInterface()
    {
        app()->singleton(BoilerplateInterface::class, BoilerplateService::class);

        $test = call(BoilerplateInterface::class)->test('test', 1);

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanProperlyCallTheServiceWithoutParameters()
    {
        $test = call(BoilerplateService::class)->test();

        $this->assertTrue($test);
    }
}
