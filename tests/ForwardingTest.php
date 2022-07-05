<?php

namespace MichaelRubel\EnhancedContainer\Tests;

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
    public function testCanForwardDomainToBuilderUsingMultipleClasses()
    {
        Forwarding::enable()
            ->from(BestDomain::class)
            ->to([
                TestModel::class,
                BestBuilder::class,
            ]);

        $call = call(BestDomain::class)->builderMethod();

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

//    /** @test */
//    public function testFailsToCallMethodInRepo()
//    {
//        $this->expectException(\TypeError::class);
//
//        $object = resolve(UserService::class);
//
//        call($object)->testMethodMultipleParamsInRepo([], '123test');
//    }
//
//    /** @test */
//    public function testCanCallMethodWithMultipleParamsInRepo()
//    {
//        $object = resolve(UserService::class);
//
//        $test = call($object)->testMethodMultipleParamsInRepo([], 123);
//
//        $this->assertTrue($test);
//    }
//
//    /** @test */
//    public function testCanCallRepoDirectlyWithMethodForwarding()
//    {
//        $object = resolve(UserRepository::class);
//
//        $test = call($object)->testMethodMultipleParamsInRepo([], 123);
//
//        $this->assertTrue($test);
//    }
//
//    /** @test */
//    public function testCanCallRepoDirectlyWithoutForwarding()
//    {
//        config([
//            'enhanced-container.forwarding_enabled' => false,
//        ]);
//
//        $object = resolve(UserRepository::class);
//
//        $test = call($object)->testMethodMultipleParamsInRepo([], 123);
//
//        $this->assertTrue($test);
//    }
//
//    /** @test */
//    public function testCanExtendMethodForwarder()
//    {
//        extend(Forwarding::class, function ($forwarder) {
//            $forwarder->test = true;
//
//            return $forwarder;
//        });
//
//        $forwarder = app(Forwarding::class, ['class' => BestDomain::class]);
//
//        $this->assertTrue($forwarder->test);
//        $this->assertIsString($forwarder->class);
//        $this->assertIsArray($forwarder->dependencies);
//    }
//
//    /** @test */
//    public function testForwarderReturnsNullIfTheSameObjectsFound()
//    {
//        $this->assertNull(
//            call(new TestModel)->getInternal(Call::FORWARDS_TO)
//        );
//
//        $this->assertNull(
//            call(TestModel::class)->getInternal(Call::FORWARDS_TO)
//        );
//    }
}
