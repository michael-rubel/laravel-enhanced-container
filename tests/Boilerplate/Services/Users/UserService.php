<?php

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\Users;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepository;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\TestServiceInterface;

class UserService implements TestServiceInterface
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
        return true;
    }
}
