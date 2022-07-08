<?php

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepository;

class TestService implements BoilerplateInterface
{
    /**
     * @param  TestRepository  $testRepository
     */
    public function __construct(
        public TestRepository $testRepository
    ) {
    }

    /**
     * @return bool
     */
    public function existingMethod(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function testMethod(): bool
    {
        return false;
    }
}
