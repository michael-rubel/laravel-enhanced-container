<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Core\MethodForwarder;
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
        MethodForwarder::from(TestService::class)->to(TestRepository::class);

        $call = call(TestService::class)->nonExistingMethod();

        $this->assertTrue($call);
    }

    /** @test */
    public function testCanForwardDomainToBuilderUsingMultipleClasses()
    {
        MethodForwarder::from(BestDomain::class)
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
        MethodForwarder::from(UserService::class)->to(UserRepository::class);

        $callProxy = call(UserService::class);

        $test = $callProxy->testProperty;
        $this->assertTrue($test);

        $callProxy->testProperty = false;
        $test = $callProxy->testProperty;
        $this->assertFalse($test);
    }

//    /** @test */
//    public function testCanSetPropertiesWithoutForwarding()
//    {
//        runWithoutForwarding(function () {
//            $callProxy = call(UserService::class);
//            $callProxy->testProperty = false;
//            $test = $callProxy->testProperty;
//            $this->assertFalse($test);
//        });
//    }
//
//    /** @test */
//    public function testThrowsErrorSettingNonExistingPropertyWithForwarding()
//    {
//        $callProxy = call(UserService::class);
//
//        $callProxy->nonExistingProperty = false;
//
//        $this->assertFalse(
//            $callProxy->getInternal(Call::FORWARDS_TO)->nonExistingProperty
//        );
//    }
//
//    /** @test */
//    public function testCanPassAnObjectWithMethodForwarding()
//    {
//        $object = resolve(UserService::class);
//
//        $test = call($object)->testMethod();
//
//        $this->assertTrue($test);
//    }
//
//    /** @test */
//    public function testMethodDoesntExist()
//    {
//        $this->expectException(\Error::class);
//
//        $object = resolve(UserService::class);
//
//        call($object)->doesntExistMethod();
//    }
//
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
//        extend(MethodForwarder::class, function ($forwarder) {
//            $forwarder->test = true;
//
//            return $forwarder;
//        });
//
//        $forwarder = app(MethodForwarder::class, ['class' => BestDomain::class]);
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
