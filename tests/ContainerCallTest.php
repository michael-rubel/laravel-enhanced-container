<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use Illuminate\Contracts\Container\BindingResolutionException;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructor;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructorPrimitive;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\ParameterOrderBoilerplate;
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

        $this->assertEquals(101, $call);
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
    public function testMethodDoesntExist()
    {
        $this->expectException(\Error::class);

        $object = resolve(UserService::class);
        call($object)->doesntExistMethod();
    }

    /** @test */
    public function testFailsToCallMethodWithWrongParameters()
    {
        $this->expectException(BindingResolutionException::class);

        $object = resolve(UserService::class);

        call($object)->testMethodWithParam();
    }

    /** @test */
    public function testCanCallMethodWithTwoParamsWhenOnlyOneExists()
    {
        $object = resolve(UserService::class);

        $test = call($object)->testMethodWithParam(123, true);

        $this->assertTrue($test);
    }

    /** @test */
    public function testFailsToCallMethodWithWrongParametersMultiple()
    {
        $this->expectException(BindingResolutionException::class);

        $object = resolve(UserService::class);
        call($object)->testMethodWithMultipleParams(123, true);
    }

    /** @test */
    public function testFailsToCallMethodWithWrongTypes()
    {
        $this->expectException(\TypeError::class);

        $object = resolve(UserService::class);

        call($object)->testMethodWithMultipleParams(123, true, 123, false);
    }

    /** @test */
    public function testFailsToCallMethodWithThreeParamsUsingFourParams()
    {
        $object = resolve(UserService::class);

        $test = call($object)->testMethodWithMultipleParams([], true, 123, false);

        $this->assertTrue($test);
    }

    /** @test */
    public function testSupportsNamedParameters()
    {
        $response = call(ParameterOrderBoilerplate::class)->handle(
            third: 'Third',
            second: 'Second',
            first: 'First'
        );

        $this->assertSame('FirstSecondThird', $response);
    }

    /** @test */
    public function testSupportsStringBindingsWithDependencies()
    {
        bind('test')->to(BoilerplateService::class);

        $response = call('test', ['dependency']);

        $this->assertInstanceOf(BoilerplateService::class, $response->getInternal(Call::INSTANCE));
    }

    /** @test */
    public function testArrayParams()
    {
        bind('test')->to(BoilerplateServiceWithConstructorPrimitive::class);

        $response = call('test', [
            'param' => false,
            'nextParam' => 'testString',
        ]);

        $this->assertFalse($response->getParam());
        $this->assertStringContainsString('testString', $response->getNextParam());
    }

    /** @test */
    public function testCanCheckIssetProperty()
    {
        $proxy = call(UserService::class);
        $this->assertTrue(isset($proxy->existingProperty));
    }

    /** @test */
    public function testCanUnsetProperty()
    {
        $proxy = call(UserService::class);
        $this->assertFalse($proxy->existingProperty);
        unset($proxy->existingProperty);
        $this->expectException(\Error::class);
        $this->assertNull($proxy->existingProperty);
    }
}
