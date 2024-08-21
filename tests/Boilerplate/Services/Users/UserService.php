<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\Users;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepository;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Services\TestServiceInterface;

class UserService implements TestServiceInterface
{
    public function __construct(
        public TestRepository $testRepository,
        public bool $existingProperty = false
    ) {}

    public function existingMethod(): bool
    {
        return true;
    }

    public function testMethod(): bool
    {
        return true;
    }

    public function testMethodWithParam(string $param): bool
    {
        return true;
    }

    public function testMethodWithMultipleParams(array $params, string $string, int $int): bool
    {
        return true;
    }
}
