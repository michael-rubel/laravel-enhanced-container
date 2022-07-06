<?php

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories;

use Illuminate\Database\Query\Builder;

class TestRepository implements TestRepositoryInterface
{
    /**
     * @param  Builder  $builder
     */
    public function __construct(
        public Builder $builder
    ) {
    }

    /**
     * @return bool
     */
    public function methodInRepository(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function nonExistingMethod(): bool
    {
        return true;
    }
}
