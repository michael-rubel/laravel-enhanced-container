<?php

namespace MichaelRubel\ContainerCall\Tests\Boilerplate\Domain\Best;

use MichaelRubel\ContainerCall\Tests\Boilerplate\Builders\Best\BestBuilder;

class BestDomain
{
    /**
     * @param BestBuilder $testRepository
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
