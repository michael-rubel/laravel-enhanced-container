<?php

namespace MichaelRubel\ContainerCall\Tests;

use MichaelRubel\ContainerCall\Tests\Boilerplate\Services\TestService;
use MichaelRubel\ContainerCall\Tests\Boilerplate\Services\Users\UserService;

class MethodForwardingTest extends TestCase
{
    /** @setUp */
    public function setUp(): void
    {
        parent::setUp();

        config([
            'container-calls.forwarding_enabled' => true,
        ]);
    }

    /** @test */
    public function testMethodNotForwardedWhenForwardingIsDisabled()
    {
        $this->expectException(\BadMethodCallException::class);

        config([
            'container-calls.forwarding_enabled' => false,
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
    public function testServiceMethodForwardsToRepositoryWithContainerResolving()
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
}
