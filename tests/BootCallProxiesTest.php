<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use Illuminate\Support\Fluent;
use MichaelRubel\EnhancedContainer\Core\CallProxy;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithBootedCallProxy;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateWithBootedCallProxyWrongParams;

class BootCallProxiesTest extends TestCase
{
    /** @test */
    public function testCanBootCallProxies()
    {
        bind(BoilerplateInterface::class)->to(BoilerplateService::class);
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
        bind(BoilerplateInterface::class)->to(BoilerplateService::class);
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
}
