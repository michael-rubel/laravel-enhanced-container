<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructor;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\Users\UserService;

class ContainerCallTest extends TestCase
{
    /** @test */
    public function testCanCallTheServiceAsString()
    {
        $test = call(BoilerplateService::class)->test('test', 1);

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanCallTheServiceAsObject()
    {
        $test = call(new BoilerplateService())->test('test', 1);

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanCallTheServiceUsingInterface()
    {
        app()->singleton(BoilerplateInterface::class, BoilerplateService::class);

        $test = call(BoilerplateInterface::class)->test('test', 1);

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanCallTheServiceWithoutParameters()
    {
        $test = call(BoilerplateService::class)->test();

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanCallTheServiceWithRequiredConstructorParams()
    {
        $call = call(BoilerplateServiceWithConstructor::class, [true])->yourMethod(100);

        $this->assertEquals(
            101,
            $call
        );
    }

    /** @test */
    public function testCanCallTheServiceWithRequiredConstructorNamedParams()
    {
        $call = call(BoilerplateServiceWithConstructor::class, ['param' => true])->yourMethod(100);

        $this->assertEquals(
            101,
            $call
        );
    }

    /** @test */
    public function testCanCallReusingCallProxyInstance()
    {
        $callProxy = call(BoilerplateServiceWithConstructor::class, ['param' => true]);

        $test = $callProxy->yourMethod(100);

        $this->assertEquals(101, $test);

        $test = $callProxy->test();

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanGetAndSetPropertiesThroughCallProxy()
    {
        $callProxy = call(BoilerplateService::class);

        $test = $callProxy->testProperty;

        $this->assertTrue($test);

        $test = $callProxy->testProperty = false;

        $this->assertFalse($test);
    }

    /** @test */
    public function testThrowsErrorAccessingNonExistingPropertyWithoutForwarding()
    {
        $this->expectException(\InvalidArgumentException::class);

        $callProxy = call(UserService::class);

        $test = $callProxy->testProperty;

        $this->assertTrue($test);
    }

    /** @test */
    public function testThrowsErrorSettingNonExistingPropertyWithoutForwarding()
    {
        $this->expectException(\InvalidArgumentException::class);

        $callProxy = call(UserService::class);

        $test = $callProxy->testProperty = false;

        $this->assertFalse($test);
    }
}
