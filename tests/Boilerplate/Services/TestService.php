<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepository;

class TestService implements BoilerplateInterface
{
    public function __construct(
        public TestRepository $testRepository
    ) {}

    public function existingMethod(): bool
    {
        return true;
    }

    public function testMethod(): bool
    {
        return false;
    }
}
