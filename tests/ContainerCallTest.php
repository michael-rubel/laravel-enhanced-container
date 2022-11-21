<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use Illuminate\Contracts\Container\BindingResolutionException;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Core\CallProxy;
use MichaelRubel\EnhancedContainer\Core\Forwarding;
use MichaelRubel\EnhancedContainer\Exceptions\InstanceInteractionException;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceResolvesContextualInMethod;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceResolvesGlobalInMethod;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructor;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructorPrimitive;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\ParameterOrderBoilerplate;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepository;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\Users\UserRepository;
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

        $this->assertEquals(101, $call);
    }

    /** @test */
    public function testCanCallTheServiceWithRequiredConstructorNamedParams()
    {
        $call = call(BoilerplateServiceWithConstructor::class, ['param' => true])->yourMethod(100);

        $this->assertEquals(101, $call);
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
        $this->app->bind('test', BoilerplateService::class);

        $response = call('test', ['dependency']);

        $this->assertInstanceOf(BoilerplateService::class, $response->getInternal(Call::INSTANCE));
    }

    /** @test */
    public function testArrayParams()
    {
        $this->app->bind('test', BoilerplateServiceWithConstructorPrimitive::class);

        $response = call('test', [
            'param' => false,
            'nextParam' => 'testString',
        ]);

        $this->assertFalse($response->getParam());
        $this->assertStringContainsString('testString', $response->getNextParam());
    }

    /** @test */
    public function testCallProxyResolvesContextualBinding()
    {
        // bind the service globally
        $this->app->singleton(BoilerplateInterface::class, BoilerplateService::class);

        $call = call(BoilerplateInterface::class);

        $this->assertInstanceOf(BoilerplateService::class, $call->getInternal(Call::INSTANCE));

        // set contextual
        $this->app->when(BoilerplateServiceResolvesContextualInMethod::class)
            ->needs(BoilerplateInterface::class)
            ->give(TestService::class);

        $this->app->when(TestRepository::class)
            ->needs(BoilerplateInterface::class)
            ->give(UserService::class);

        $service = call(BoilerplateServiceResolvesContextualInMethod::class);

        $this->assertInstanceOf(TestService::class, $service->constructorHasContextual());
        $this->assertInstanceOf(TestService::class, $service->methodHasContextual()->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(TestService::class, $service->methodHasContextual2()->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(UserService::class, $service->methodHasContextual3()->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(BoilerplateService::class, $service->methodHasGlobal()->getInternal(Call::INSTANCE));

        // ensure global still available for other classes
        $service = call(BoilerplateServiceResolvesGlobalInMethod::class);
        $this->assertInstanceOf(BoilerplateService::class, $service->getsGlobalBinding()->getInternal(Call::INSTANCE));
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

    /** @test */
    public function testIssetPropertyWithForwarding()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $proxy = call(UserService::class);
        $this->assertTrue(isset($proxy->existingProperty));
        $this->assertTrue($proxy->testProperty);
        $this->expectException(InstanceInteractionException::class);
        $proxy->existingProperty;
    }

    /** @test */
    public function testUnsetPropertyWithForwarding()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $proxy = call(UserService::class);
        unset($proxy->existingProperty);
        $this->assertTrue($proxy->testProperty);
        $this->expectException(InstanceInteractionException::class);
        $proxy->existingProperty;
    }

    /** @test */
    public function testCanOverrideMethodsInCallProxy()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $call = new TestCallProxy(UserService::class);
        $this->assertTrue($call->existingMethod());
        $call->nonExistingMethod();
    }

    /** @test */
    public function testParsesNonAssociativeArraysWhenResolvingDependencies()
    {
        $repo = app(TestRepository::class);
        $call = call(UserService::class, [0 => app(TestRepository::class), 1 => true]);
        $this->assertEquals($call->testRepository, $repo);
        $this->assertTrue($call->existingProperty);
    }
}

class TestCallProxy extends CallProxy
{
    protected function containerCall(object $service, string $method, array $parameters): mixed
    {
        return parent::containerCall($service, $method, $parameters);
    }

    public function __call(string $method, array $parameters): mixed
    {
        if (! method_exists($this->instance, $method)) {
            if ($this->hasPreviousInteraction($method)) {
                throw new InstanceInteractionException;
            }

            $this->findForwardingInstance();
        }

        $this->interact($method, Call::METHOD);

        return $this->handleMissing(
            fn () => $this->containerCall($this->instance, $method, $parameters),
            by: 'Call to undefined method'
        );
    }
}
