<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\Users;

use Illuminate\Database\Query\Builder;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepositoryInterface;

class UserRepository implements TestRepositoryInterface
{
    public bool $testProperty = true;

    public function __construct(
        public Builder $builder
    ) {
    }

    public function methodInRepository(): bool
    {
        return true;
    }

    public function nonExistingMethod(): bool
    {
        return true;
    }

    public function testMethodMultipleParamsInRepo(array $params, int $int): bool
    {
        return true;
    }
}
