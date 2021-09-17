<?php

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepository;

class TestService
{
    /**
     * @param TestRepository $testRepository
     */
    public function __construct(
        public TestRepository $testRepository
    ) {
    }

    /**
     * @return bool
     */
    public function testMethod(): bool
    {
        return false;
    }
}
