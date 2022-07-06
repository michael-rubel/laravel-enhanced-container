<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use Illuminate\Database\QueryException;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Core\Forwarding;
use MichaelRubel\EnhancedContainer\Exceptions\InstanceInteractionException;
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
        $proxy = call(TestService::class);
        $proxy->test = true;
        $this->assertTrue($proxy->test);

        $proxy = call(TestService::class);
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
        $test = $callProxy->testProperty;
        $this->assertTrue($test);
        $this->assertInstanceOf(UserRepository::class, $callProxy->getInternal(Call::INSTANCE));

        $callProxy->testProperty = false;
        $test = $callProxy->testProperty;
        $this->assertFalse($test);
        $this->assertInstanceOf(UserRepository::class, $callProxy->getInternal(Call::INSTANCE));
    }

    /** @test */
    public function testCanSetPropertiesWithoutForwarding()
    {
        $callProxy = call(UserService::class);
        $callProxy->testProperty = false;
        $test = $callProxy->testProperty;
        $this->assertFalse($test);
    }

    /** @test */
    public function testSetsNonExistingPropertyWithForwarding()
    {
        // Test without forwarding.
        $callProxy = call(UserService::class);
        $callProxy->testRepository = app(TestRepository::class);
        $this->assertInstanceOf(UserService::class, $callProxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(TestRepository::class, $callProxy->testRepository);

        // Now define it.
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        // Initial state, because property exists.
        $callProxy = call(UserService::class);
        $callProxy->testRepository = app(TestRepository::class);
        $this->assertInstanceOf(TestRepository::class, $callProxy->testRepository);

        // Now we're accessing non-existing property, so
        // the instance is swapped to the new one from the forwarder.
        $callProxy->nonExistingProperty = true;
        $this->assertInstanceOf(UserRepository::class, $callProxy->getInternal(Call::INSTANCE));
        $this->assertTrue($callProxy->getInternal(Call::INSTANCE)->nonExistingProperty);

        // todo: keep a previous instance?
    }

    /** @test */
    public function testCanPassAnObjectWithMethodForwarding()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $object = resolve(UserService::class);
        $test = call($object)->testMethod();
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
        $test = call($object)->testMethodMultipleParamsInRepo([], 123);
        $this->assertTrue($test);
    }

    /** @test */
    public function testCanCallRepoDirectlyWithMethodForwarding()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $object = resolve(UserRepository::class);
        $test = call($object)->testMethodMultipleParamsInRepo([], 123);
        $this->assertTrue($test);
    }

    /** @test */
    public function testCanCallRepoDirectlyWithoutForwarding()
    {
        $object = resolve(UserRepository::class);
        $test = call($object)->testMethodMultipleParamsInRepo([], 123);
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

        // The test throws the exception and it's excepted since we don't have
        // any DB connection but it says the `find` method actually works.
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
            ->to(TestModel::class);

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
        $this->expectException(QueryException::class);
        $proxy->find(1);
    }

    /** @test */
    public function testChainedForwardingWithMissingMethods()
    {
        // Define a chained forwarding.
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class)
            ->from(UserRepository::class)
            ->to(TestModel::class);

        // Make the service through CallProxy.
        $proxy = call(UserService::class);

        // Call method directly on the service.
        $test = $proxy->existingMethod();
        $this->assertTrue($test);
        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::INSTANCE));

        // Call method that exists in the repository
        // assigned to the service in forwarding above.
        $test = $proxy->nonExistingInRepositoryMethod();
        $this->assertTrue($test);
        $this->assertInstanceOf(TestModel::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(UserRepository::class, $proxy->getInternal(Call::PREVIOUS));
    }

    /** @test */
    public function testStateMachineForProperties()
    {
        // Define a chained forwarding.
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        // Make the service through CallProxy.
        $proxy = call(UserService::class);

        // Set property to base instance.
        $proxy->existingProperty = true;
        $this->assertTrue($proxy->existingProperty);
        $this->assertInstanceOf(UserService::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertSame(Call::SET, $proxy->getInternal(Call::STATE)['existingProperty']);

        // Swaps instance.
        $proxy->nonExistingProperty = true;
        $this->assertTrue($proxy->nonExistingProperty);
        $this->assertInstanceOf(UserRepository::class, $proxy->getInternal(Call::INSTANCE));

        // Should throw an exception because we previously changed the state.
        $this->expectException(InstanceInteractionException::class);
        $this->assertTrue($proxy->existingProperty);
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
        $this->assertSame(Call::METHOD, $proxy->getInternal(Call::STATE)['existingMethod']);

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
        bind(TestServiceInterface::class)->to(TestService::class);
        bind(TestRepositoryInterface::class)->to(TestRepository::class);

        Forwarding::enable()
            ->from(TestServiceInterface::class)
            ->to(TestRepositoryInterface::class);

        $proxy = call(TestService::class);

        $this->assertTrue($proxy->existingMethod());
        $this->assertInstanceOf(TestService::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertSame(Call::METHOD, $proxy->getInternal(Call::STATE)['existingMethod']);

        $this->assertTrue($proxy->nonExistingMethod());
        $this->assertInstanceOf(TestRepository::class, $proxy->getInternal(Call::INSTANCE));
        $this->assertInstanceOf(TestService::class, $proxy->getInternal(Call::PREVIOUS));
        $this->assertTrue($proxy->methodInRepository());
    }
}
