<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use Illuminate\Support\Fluent;
use MichaelRubel\EnhancedContainer\Core\CallProxy;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateDependenciesAssignedOldWay;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithBootedCallProxy;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithBootedCallProxyWithMethod;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateWithBootedCallProxyWrongParams;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Domain\Best\BestDomain;

class BootCallProxiesTest extends TestCase
{
    /** @test */
    public function testCanBootCallProxies()
    {
        $this->app->bind(BoilerplateInterface::class, BoilerplateService::class);
        $call = call(BoilerplateServiceWithBootedCallProxy::class);

        $test = $call->getProxy();
        $this->assertInstanceOf(Fluent::class, $test);

        $test = $call->getProxiedClass();
        $this->assertInstanceOf(CallProxy::class, $test);

        $test = $call->getOriginal();
        $this->assertInstanceOf(BoilerplateService::class, $test);
    }

    /** @test */
    public function testBootCallProxiesIgnoresParamsIfNotAClass()
    {
        $this->app->bind(BoilerplateInterface::class, BoilerplateService::class);
        $call = call(BoilerplateWithBootedCallProxyWrongParams::class, ['test', true]);

        $test = $call->getProxy();
        $this->assertInstanceOf(Fluent::class, $test);

        $test = $call->getProxiedClass();
        $this->assertInstanceOf(CallProxy::class, $test);

        $test = $call->getOriginal();
        $this->assertInstanceOf(BoilerplateService::class, $test);

        $originalClass = resolve(BoilerplateWithBootedCallProxyWrongParams::class, [
            'test' => 'test', 'boolean' => true,
        ]);

        $this->assertObjectHasAttribute('proxy', $originalClass);
        $this->assertObjectHasAttribute('test', $originalClass);
        $this->assertObjectHasAttribute('boolean', $originalClass);
    }

    /** @test */
    public function testBootCallProxiesWithUnassignedDependencies()
    {
        $this->app->bind(BoilerplateInterface::class, BoilerplateService::class);
        $call = call(BoilerplateDependenciesAssignedOldWay::class);

        $test = $call->getProxy();
        $this->assertInstanceOf(Fluent::class, $test);

        $test = $call->getProxiedClass();
        $this->assertInstanceOf(CallProxy::class, $test);

        $originalClass = resolve(BoilerplateDependenciesAssignedOldWay::class);

        $this->assertObjectHasAttribute('proxy', $originalClass);
    }

    /** @test */
    public function testBootCallProxiesFromAnyMethod()
    {
        $this->app->bind(BoilerplateInterface::class, BoilerplateService::class);
        $call = call(BoilerplateServiceWithBootedCallProxyWithMethod::class);

        $bestDomain = $call->handle();

        $this->assertInstanceOf(BestDomain::class, $bestDomain);
    }

    /** @test */
    public function testBootCallProxiesPreventsOverlapping()
    {
        $this->app->bind(BoilerplateInterface::class, BoilerplateService::class);
        $call = call(BoilerplateServiceWithBootedCallProxy::class);

        $bestDomain = $call->handle();

        $this->assertInstanceOf(BestDomain::class, $bestDomain);
    }
}
