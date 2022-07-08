<?php

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\Users;

use Illuminate\Database\Query\Builder;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepositoryInterface;

class UserRepository implements TestRepositoryInterface
{
    /**
     * @var bool
     */
    public bool $testProperty = true;

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

    /**
     * @param  array  $params
     * @param  int  $int
     *
     * @return bool
     */
    public function testMethodMultipleParamsInRepo(array $params, int $int): bool
    {
        return true;
    }
}
