<?php

namespace MichaelRubel\ContainerCall\Tests\Boilerplate\Services;

use MichaelRubel\ContainerCall\Tests\Boilerplate\Repositories\TestRepository;

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
