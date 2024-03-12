<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Domain\Best;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Builder\Best\BestBuilder;

class BestDomain
{
    public function __construct(
        public BestBuilder $testRepository
    ) {
    }

    public function bestMethod(): bool
    {
        return true;
    }
}
