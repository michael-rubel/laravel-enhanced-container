<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use Illuminate\Database\QueryException;
use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Core\Forwarding;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Builder\Best\BestBuilder;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Domain\Best\BestDomain;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Models\TestModel;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepository;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\Users\UserRepository;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\TestService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\Users\UserService;

class ForwardingTest extends TestCase
{
    /** @test */
    public function testCanCallMethodWithoutForwardingToRepositoryIfMethodExistsInTheService()
    {
        $call = call(TestService::class)->testMethod();

        $this->assertFalse($call);
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
    public function testThrowsErrorSettingNonExistingPropertyWithForwarding()
    {
        Forwarding::enable()
            ->from(UserService::class)
            ->to(UserRepository::class);

        $callProxy = call(UserService::class);
        $callProxy->nonExistingProperty = false;
        $this->assertInstanceOf(UserRepository::class, $callProxy->getInternal(Call::INSTANCE));
        $this->assertFalse($callProxy->getInternal(Call::INSTANCE)->nonExistingProperty);
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
}
