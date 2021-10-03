<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use Illuminate\Contracts\Container\BindingResolutionException;
use MichaelRubel\EnhancedContainer\Exceptions\PropertyNotFoundException;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructor;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\Users\UserService;

class ContainerTest extends TestCase
{
    /** @test */
    public function testCanGetMethodBindings()
    {
        $methodBindings = app()->getMethodBindings();

        $this->assertIsArray($methodBindings);
    }
}
