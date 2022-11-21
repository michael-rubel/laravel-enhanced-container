<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use Illuminate\Database\QueryException;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Core\Forwarding;
use MichaelRubel\EnhancedContainer\Exceptions\InstanceInteractionException;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Builder\Best\BestBuilder;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Models\TestModel;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepository;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepositoryInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\Users\UserRepository;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\TestService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\TestServiceInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\Users\UserService;

class ForwardingTest extends TestCase
{
    /** @test */
    public function testCanCallMethodWithoutForwarding()
    {
        $call = call(TestService::class)->testMethod();
        $this->assertFalse($call);

        $this->expectException(\Error::class);
        call(TestService::class)->nonExistingMethod();
    }

    /** @test */
    public function testCanGetAndSetPropertyWithoutForwarding()
    {
        $proxy       = call(TestService::class);
        $proxy->test = true;
        $this->assertTrue($proxy->test);

        $proxy       = call(TestService::class);
        $proxy->test = false;
        $this->assertFalse($proxy->test);
    }

    /** @test */
    public function testServiceForwardsToRepositoryWhenMethodDoesntExist()
    {
        Forwarding::enable()
            ->from(TestService::class)
            ->to(TestRepository::class);

        $call = call(TestService::class)->nonExistingMethod();

        $this->assertTrue($call);
    }

    /** @test */
    public function testCanGetAndSetPropertiesWithForwarding()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $callProxy = call(UserService::class);
        $test      = $callProxy->testProperty;
        $this->assertTrue($test);
        $this->assertInstanceOf(UserRepository::class, $callProxy->getInternal(Call::INSTANCE));

        $callProxy->testProperty = false;
        $test                    = $callProxy->testProperty;
        $this->assertFalse($test);
        $this->assertInstanceOf(UserRepository::class, $callProxy->getInternal(Call::INSTANCE));
    }

    /** @test */
    public function testSetsNonExistingProperty()
    {
        $callProxy               = call(UserService::class);
        $callProxy->testProperty = true;
        $this->assertTrue($callProxy->testProperty);
        $this->assertInstanceOf(UserService::class, $callProxy->getInternal(Call::INSTANCE));
    }

    /** @test */
    public function testCanPassAnObjectWithMethodForwarding()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $object = resolve(UserService::class);
        $test   = call($object)->testMethod();

        $this->assertTrue($test);
    }

    /** @test */
    public function testMethodDoesntExist()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $this->expectException(\Error::class);

        $object = resolve(UserService::class);
        call($object)->doesntExistMethod();
    }

    /** @test */
    public function testFailsToCallMethodInRepo()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $this->expectException(\TypeError::class);

        $object = resolve(UserService::class);
        call($object)->testMethodMultipleParamsInRepo([], '123test');
    }

    /** @test */
    public function testCanCallMethodWithMultipleParamsInRepo()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $object = resolve(UserService::class);
        $test   = call($object)->testMethodMultipleParamsInRepo([], 123);

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanCallRepoDirectlyWithMethodForwarding()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $object = resolve(UserRepository::class);
        $test   = call($object)->testMethodMultipleParamsInRepo([], 123);

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanCallRepoDirectlyWithoutForwarding()
    {
        $object = resolve(UserRepository::class);
        $test   = call($object)->testMethodMultipleParamsInRepo([], 123);

        $this->assertTrue($test);
    }

    /** @test */
    public function testContainerCallRedirectsManuallyIfCannotFindTheMethod()
    {
        Forwarding::enable()
            ->from(TestService::class)
            ->to(TestModel::class);

        $this->expectException(QueryException::class);

        // TestService redirects to the model.
        // The container cannot call it, so we're forwarding the method manually.
        call(TestService::class)->find(1);

        // The test throws the exception, and it's excepted since we don't have
        // any DB connection, but it says the `find` method actually works.
    }

    /** @test */
    public function testReflectionExceptionIsThrownWhenManualForwardingIsDisabled()
    {
        $this->expectException(\BadMethodCallException::class);

        call(TestService::class)->find(1);
    }

    /** @test */
    public function testChainedForwarding()
    {
        // Define a chained forwarding.
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class)
            ->from(UserRepository::class)
            ->to(TestModel::class)
            ->from(TestModel::class)
            ->to(BestBuilder::class);

        // Make the service through CallProxy.
        $proxy = call(UserService::class);

        // Call method directly on the service.
        $test = $proxy->existingMethod();
        $this->assertTrue($test);
        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::INSTANCE));

        // Call method that exists in the repository
        // assigned to the service in forwarding above.
        $test = $proxy->nonExistingMethod();
        $this->assertTrue($test);
        $this->assertInstanceOf(UserRepository::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::PREVIOUS));

        // Call the method that only exists on the model,
        // i.e. on the third element in the forwarding chain.
        $this->assertTrue($proxy->nonExistingInRepositoryMethod());
        $this->assertInstanceOf(TestModel::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(UserRepository::class, $proxy->getInternal(Call::PREVIOUS));

        // Call the method that only exists on the builder,
        // i.e. on the fourth element in the forwarding chain.
        $this->assertTrue($proxy->builderMethod());
        $this->assertInstanceOf(BestBuilder::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(TestModel::class, $proxy->getInternal(Call::PREVIOUS));
        $this->expectException(QueryException::class);
        $proxy->builder->find(1);
    }

    /** @test */
    public function testChainedForwardingWithMissingMethods()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class)
            ->from(UserRepository::class)
            ->to(TestModel::class);

        $proxy = call(UserService::class);

        $test = $proxy->existingMethod();
        $this->assertTrue($test);
        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::INSTANCE));

        $test = $proxy->nonExistingInRepositoryMethod();
        $this->assertTrue($test);
        $this->assertInstanceOf(TestModel::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(UserRepository::class, $proxy->getInternal(Call::PREVIOUS));
    }

    /** @test */
    public function testChainedForwardingWithMissingProperties()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class)
            ->from(UserRepository::class)
            ->to(TestModel::class);

        $proxy = call(UserService::class);

        $test = $proxy->existingProperty;
        $this->assertFalse($test);
        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::INSTANCE));

        $test = $proxy->nonInServiceExistingProperty;
        $this->assertTrue($test);
        $this->assertInstanceOf(TestModel::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(UserRepository::class, $proxy->getInternal(Call::PREVIOUS));
    }

    /** @test */
    public function testStatesForProperties()
    {
        // Define a chained forwarding.
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        // Make the service through CallProxy.
        $proxy = call(UserService::class);

        // Set property to base instance.
        $proxy->existingProperty = true;
        $this->assertSame(Call::SET, $proxy->getInternal(Call::INTERACTIONS)['existingProperty']);
        $this->assertTrue($proxy->existingProperty);
        $this->assertSame(Call::GET, $proxy->getInternal(Call::INTERACTIONS)['existingProperty']);

        $proxy->nonExistingProperty = true;
        $this->assertSame(Call::SET, $proxy->getInternal(Call::INTERACTIONS)['nonExistingProperty']);
        $this->assertTrue($proxy->nonExistingProperty);
        $this->assertSame(Call::GET, $proxy->getInternal(Call::INTERACTIONS)['nonExistingProperty']);

        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::INSTANCE));
    }

    /** @test */
    public function testFailsWhenTryingToNonExistingCallMethodAndInteractedPreviously()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $proxy = call(UserService::class);

        $proxy->nonExistingProperty = true;
        $this->assertTrue($proxy->nonExistingProperty);

        $proxy->nonExistingMethod();
        $this->assertInstanceOf(UserRepository::class, $proxy->getInternal(Call::INSTANCE));

        $this->expectException(InstanceInteractionException::class);
        $this->assertTrue($proxy->nonExistingProperty);
    }

    /** @test */
    public function testStateMachineForMethods()
    {
        // Define a chained forwarding.
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        // Make the service through CallProxy.
        $proxy = call(UserService::class);

        // Set property to base instance.
        $this->assertTrue($proxy->existingMethod());
        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertSame(Call::METHOD, $proxy->getInternal(Call::INTERACTIONS)['existingMethod']);

        // Swaps instance.
        $this->assertTrue($proxy->nonExistingMethod());
        $this->assertInstanceOf(UserRepository::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::PREVIOUS));
        $this->assertTrue($proxy->methodInRepository());

        // Should throw an exception because we previously changed the state.
        $this->expectException(InstanceInteractionException::class);
        $proxy->existingMethod();
    }

    /** @test */
    public function tesForwardingResolvesInterfaces()
    {
        $this->app->bind(TestServiceInterface::class, TestService::class);
        $this->app->bind(TestRepositoryInterface::class, TestRepository::class);

        Forwarding::enable()
            ->from(TestServiceInterface::class)
            ->to(TestRepositoryInterface::class);

        $proxy = call(TestService::class);

        $this->assertTrue($proxy->existingMethod());
        $this->assertInstanceOf(TestService::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertSame(Call::METHOD, $proxy->getInternal(Call::INTERACTIONS)['existingMethod']);

        $this->assertTrue($proxy->nonExistingMethod());
        $this->assertInstanceOf(TestRepository::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(TestService::class, $proxy->getInternal(Call::PREVIOUS));
        $this->assertTrue($proxy->methodInRepository());
    }

    /** @test */
    public function testCanSetPrevious()
    {
        // Define a chained forwarding.
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        // Make the service through CallProxy.
        $proxy = call(UserService::class);

        // Set property to base instance.
        $this->assertTrue($proxy->existingMethod());
        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertNull($proxy->getInternal(Call::PREVIOUS));

        // Swaps instance.
        $this->assertTrue($proxy->nonExistingMethod());
        $this->assertInstanceOf(UserRepository::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::PREVIOUS));

        // Instance swapped, but we want to set previous.
        $proxy->setPrevious();
        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(UserRepository::class, $proxy->getInternal(Call::PREVIOUS));
    }

    /** @test */
    public function testCanDisableForwardingOnProxyLevel()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $proxy = call(UserService::class);

        // Doesn't swap the instance because we manually turned out forwarding on this instance.
        $proxy->disableForwarding();

        $this->expectException(\Error::class);
        $proxy->nonExistingMethod();
    }

    /** @test */
    public function testCanEnableDisabledForwardingOnProxyLevel()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $proxy = call(UserService::class);

        $proxy->disableForwarding();
        $this->assertFalse($proxy->getInternal(Call::FORWARDING));

        $proxy->enableForwarding();
        $this->assertTrue($proxy->getInternal(Call::FORWARDING));
    }

    /** @test */
    public function testCanForwardingExtension()
    {
        $forwarding = new TestForwarding;
        $forwarding->from(UserService::class)->to(UserRepository::class);

        $proxy = call(UserService::class);
        $this->assertTrue($proxy->getInternal(Call::FORWARDING));
    }
}

class TestForwarding extends Forwarding
{
    public function to(string $destination): static
    {
        app()->bind(
            abstract: $this->pendingClass . static::CONTAINER_KEY,
            concrete: $this->resolve($destination)
        );

        return $this;
    }
}
