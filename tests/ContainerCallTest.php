<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\QueryException;
use MichaelRubel\EnhancedContainer\Exceptions\PropertyNotFoundException;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructor;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\ParameterOrderBoilerplate;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\TestService;
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
        $this->expectException(\ErrorException::class);

        $callProxy = call(UserService::class);

        $callProxy->testProperty;
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
    public function testContainerCallRedirectsManuallyIfCannotFindTheMethod()
    {
        $this->expectException(QueryException::class);

        // Set up forwarding and the layer to redirect.
        config([
            'enhanced-container.forwarding_enabled' => true,
            'enhanced-container.manual_forwarding'  => true,
            'enhanced-container.to.layer'           => 'Model',
        ]);

        // TestService redirects to the model.
        // The container cannot call it, so we're forwarding the method manually.
        call(TestService::class)->find(1);

        // The test throws the exception and it's excepted since we don't have
        // any DB connection but it says the `find` method actually works.
    }

    /** @test */
    public function testReflectionExceptionIsThrownWhenManualForwardingIsDisabled()
    {
        $this->expectException(\ReflectionException::class);

        config([
            'enhanced-container.forwarding_enabled' => true,
            'enhanced-container.manual_forwarding'  => false,
            'enhanced-container.to.layer'           => 'Model',
        ]);

        call(TestService::class)->find(1);
    }

    /** @test */
    public function testParametersOrderIsHandledByTheContainer()
    {
        $data = [
            'second' => 'Second',
            'third'  => 'Third',
            'first'  => 'First',
        ];

        $response = call(ParameterOrderBoilerplate::class)->handle($data);
        $this->assertSame('FirstSecondThird', $response);

        $response = call(ParameterOrderBoilerplate::class)->handle();
        $this->assertSame('123', $response);

        $response = call(ParameterOrderBoilerplate::class)->getData($data);
        $this->assertSame($data, $response);

        $data = [
            'second' => 'Second',
            'first'  => 'First',
        ];

        $response = call(ParameterOrderBoilerplate::class)->handleTwo($data);
        $this->assertSame('FirstSecond', $response);

        $response = call(ParameterOrderBoilerplate::class)->getString('test', '-next');
        $this->assertSame('test-next', $response);
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
}
