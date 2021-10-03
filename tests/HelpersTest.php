<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;

class HelpersTest extends TestCase
{
    /** @test */
    public function testCanSetBindItself()
    {
        bind(BoilerplateService::class)->itself();

        $boilerplateOne = resolve(BoilerplateService::class);
        $boilerplateTwo = resolve(BoilerplateService::class);

        $this->assertNotSame($boilerplateOne, $boilerplateTwo);
    }

    /** @test */
    public function testCanSetSingleton()
    {
        singleton(BoilerplateService::class);

        $boilerplateOne = resolve(BoilerplateService::class);
        $boilerplateTwo = resolve(BoilerplateService::class);

        $this->assertSame($boilerplateOne, $boilerplateTwo);
    }

    /** @test */
    public function testCanSetScopedInstance()
    {
        scoped(BoilerplateService::class);

        $boilerplateOne = resolve(BoilerplateService::class);
        $boilerplateTwo = resolve(BoilerplateService::class);

        $this->assertSame($boilerplateOne, $boilerplateTwo);
    }
}
