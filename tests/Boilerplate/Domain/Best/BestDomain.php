<?php

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Domain\Best;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Builder\Best\BestBuilder;

class BestDomain
{
    /**
     * @param  BestBuilder  $testRepository
     */
    public function __construct(
        public BestBuilder $testRepository
    ) {
    }

    /**
     * @return bool
     */
    public function bestMethod(): bool
    {
        return true;
    }
}
