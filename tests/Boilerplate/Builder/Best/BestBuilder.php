<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Builder\Best;

use Illuminate\Database\Query\Builder;

class BestBuilder
{
    public function __construct(
        public Builder $builder
    ) {
    }

    public function builderMethod(): bool
    {
        return true;
    }
}
