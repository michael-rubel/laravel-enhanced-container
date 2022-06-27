<?php

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Builder\Best;

use Illuminate\Database\Query\Builder;

class BestBuilder
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
    public function builderMethod(): bool
    {
        return true;
    }
}
