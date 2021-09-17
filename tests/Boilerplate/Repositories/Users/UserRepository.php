<?php

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\Users;

use Illuminate\Database\Query\Builder;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepositoryInterface;

class UserRepository implements TestRepositoryInterface
{
    /**
     * @param Builder $builder
     */
    public function __construct(
        public Builder $builder
    ) {
    }

    /**
     * @return bool
     */
    public function nonExistingMethod(): bool
    {
        return true;
    }
}
