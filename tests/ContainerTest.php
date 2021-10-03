<?php

namespace MichaelRubel\EnhancedContainer\Tests;

class ContainerTest extends TestCase
{
    /** @test */
    public function testCanGetMethodBindings()
    {
        $methodBindings = app()->getMethodBindings();

        $this->assertIsArray($methodBindings);
    }
}
