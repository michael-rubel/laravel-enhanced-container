<?php

namespace MichaelRubel\ContainerCall\Tests;

use MichaelRubel\ContainerCall\Tests\Boilerplate\Repositories\TestRepository;
use MichaelRubel\ContainerCall\Tests\Boilerplate\Repositories\TestRepositoryInterface;
use MichaelRubel\ContainerCall\Tests\Boilerplate\Services\TestService;

class MethodForwardingTest extends TestCase
{
    /** @setUp */
    public function setUp(): void
    {
        parent::setUp();

        config([
            'container-calls.forwarding_enabled' => true,
            'container-calls.app' => 'MichaelRubel\ContainerCall\Tests\Boilerplate',
        ]);
    }

    /** @test */
    public function testMethodNotForwardedWhenForwardingIsDisabled()
    {
        $this->expectException(\Error::class);

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
        app()->singleton(TestRepositoryInterface::class, TestRepository::class);

        $call = call(TestService::class)->nonExistingMethod();

        $this->assertTrue($call);
    }

    /** @test */
    public function testServiceMethodForwardsToRepositoryWhenDoesntExist()
    {
        $call = call(TestService::class)->nonExistingMethod();

        $this->assertTrue($call);
    }
}
