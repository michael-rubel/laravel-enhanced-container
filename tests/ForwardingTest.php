<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Domain\Best\BestDomain;
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
        $this->expectException(\BadMethodCallException::class);

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
            'enhanced-container.naming' => 'singular',
            'enhanced-container.from' => 'Domain',
            'enhanced-container.to' => 'Builder',
        ]);

        $call = call(BestDomain::class)->builderMethod();

        $this->assertTrue($call);
    }

    /** @test */
    public function testFailToForwardDomainToBuilderWithPluralNames()
    {
        $this->expectException(\BadMethodCallException::class);

        config([
            'enhanced-container.naming' => 'plural',
            'enhanced-container.from' => 'Domain',
            'enhanced-container.to' => 'Builder',
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
    public function testThrowsErrorSettingNonExistingPropertyWithForwarding()
    {
        $this->expectException(\InvalidArgumentException::class);

        $callProxy = call(UserService::class);

        $test = $callProxy->nonExistingProperty = false;

        $this->assertFalse($test);
    }
}
