<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories;

use Illuminate\Database\Query\Builder;

class TestRepository implements TestRepositoryInterface
{
    public function __construct(
        public Builder $builder
    ) {}

    public function methodInRepository(): bool
    {
        return true;
    }

    public function nonExistingMethod(): bool
    {
        return true;
    }
}
