<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Core\MethodForwarder;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Domain\Best\BestDomain;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Models\TestModel;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\Users\UserRepository;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\TestService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\Users\UserService;

class ForwardingTest extends TestCase
{
    /** @setUp */
    public function setUp(): void
    {
        parent::setUp();

        config([
            'enhanced-container.forwarding_enabled' => true,
        ]);
    }

    /** @test */
    public function testMethodNotForwardedWhenForwardingIsDisabled()
    {
        $this->expectException(\Error::class);

        config([
            'enhanced-container.forwarding_enabled' => false,
        ]);

        call(TestService::class)->nonExistingMethod();
    }

    /** @test */
    public function testCanCallMethodWithoutForwardToRepositoryIfMethodExistsInTheService()
    {
        $call = call(TestService::class)->testMethod();

        $this->assertFalse($call);
    }

    /** @test */
    public function testServiceMethodForwardsToRepositoryWithContainerResolution()
    {
        $call = call(TestService::class)->nonExistingMethod();

        $this->assertTrue($call);
    }

    /** @test */
    public function testServiceMethodForwardsToRepositoryWhenDoesntExist()
    {
        $call = call(TestService::class)->nonExistingMethod();

        $this->assertTrue($call);
    }

    /** @test */
    public function testCanCallServiceFromSubdirectoryWithForwardingEnabled()
    {
        $call = call(UserService::class)->testMethod();

        $this->assertTrue($call);
    }

    /** @test */
    public function testCanForwardToRepositoryWithSubdirectory()
    {
        $call = call(UserService::class)->nonExistingMethod();

        $this->assertTrue($call);
    }

    /** @test */
    public function testCanForwardDomainToBuilder()
    {
        config([
            'enhanced-container.from.layer' => 'Domain',
            'enhanced-container.from.naming' => 'singular',
            'enhanced-container.to.layer' => 'Builder',
            'enhanced-container.to.naming' => 'singular',
        ]);

        $call = call(BestDomain::class)->builderMethod();

        $this->assertTrue($call);
    }

    /** @test */
    public function testFailToForwardDomainToBuilderWithPluralNames()
    {
        $this->expectException(\Error::class);

        config([
            'enhanced-container.from.layer' => 'Domain',
            'enhanced-container.from.naming' => 'pluralStudly',
            'enhanced-container.to.layer' => 'Builder',
            'enhanced-container.ti.naming' => 'pluralStudly',
        ]);

        call(BestDomain::class)->builderMethod();
    }

    /** @test */
    public function testFailToForwardDomainToBuilderWithDifferentNames()
    {
        $this->expectException(\Error::class);

        config([
            'enhanced-container.from.layer' => 'Domain',
            'enhanced-container.from.naming' => 'singular',
            'enhanced-container.to.layer' => 'Builder',
            'enhanced-container.ti.naming' => 'pluralStudly',
        ]);

        call(BestDomain::class)->builderMethod();
    }

    /** @test */
    public function testCanGetAndSetPropertiesWithForwarding()
    {
        $callProxy = call(UserService::class);

        $test = $callProxy->testProperty;
        $this->assertTrue($test);

        $callProxy->testProperty = false;
        $test = $callProxy->testProperty;
        $this->assertFalse($test);
    }

    /** @test */
    public function testCanSetPropertiesWithoutForwarding()
    {
        runWithoutForwarding(function () {
            $callProxy = call(UserService::class);
            $callProxy->testProperty = false;
            $test = $callProxy->testProperty;
            $this->assertFalse($test);
        });
    }

    /** @test */
    public function testThrowsErrorSettingNonExistingPropertyWithForwarding()
    {
        $callProxy = call(UserService::class);

        $callProxy->nonExistingProperty = false;

        $this->assertFalse(
            $callProxy->getInternal(Call::FORWARDS_TO)->nonExistingProperty
        );
    }

    /** @test */
    public function testCanPassAnObjectWithMethodForwarding()
    {
        $object = resolve(UserService::class);

        $test = call($object)->testMethod();

        $this->assertTrue($test);
    }

    /** @test */
    public function testMethodDoesntExist()
    {
        $this->expectException(\Error::class);

        $object = resolve(UserService::class);

        call($object)->doesntExistMethod();
    }

    /** @test */
    public function testFailsToCallMethodInRepo()
    {
        $this->expectException(\TypeError::class);

        $object = resolve(UserService::class);

        call($object)->testMethodMultipleParamsInRepo([], '123test');
    }

    /** @test */
    public function testCanCallMethodWithMultipleParamsInRepo()
    {
        $object = resolve(UserService::class);

        $test = call($object)->testMethodMultipleParamsInRepo([], 123);

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanCallRepoDirectlyWithMethodForwarding()
    {
        $object = resolve(UserRepository::class);

        $test = call($object)->testMethodMultipleParamsInRepo([], 123);

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanCallRepoDirectlyWithoutForwarding()
    {
        config([
            'enhanced-container.forwarding_enabled' => false,
        ]);

        $object = resolve(UserRepository::class);

        $test = call($object)->testMethodMultipleParamsInRepo([], 123);

        $this->assertTrue($test);
    }

    /** @test */
    public function testCanExtendMethodForwarder()
    {
        extend(MethodForwarder::class, function ($forwarder) {
            $forwarder->test = true;

            return $forwarder;
        });

        $forwarder = app(MethodForwarder::class, ['class' => BestDomain::class]);

        $this->assertTrue($forwarder->test);
        $this->assertIsString($forwarder->class);
        $this->assertIsArray($forwarder->dependencies);
    }

    /** @test */
    public function testForwarderReturnsNullIfTheSameObjectsFound()
    {
        $this->assertNull(
            call(new TestModel)->getInternal(Call::FORWARDS_TO)
        );

        $this->assertNull(
            call(TestModel::class)->getInternal(Call::FORWARDS_TO)
        );
    }
}
