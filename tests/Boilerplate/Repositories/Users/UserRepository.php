<?php

namespace MichaelRubel\ContainerCall\Tests\Boilerplate\Repositories\Users;

use Illuminate\Database\Query\Builder;
use MichaelRubel\ContainerCall\Tests\Boilerplate\Repositories\TestRepositoryInterface;

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
