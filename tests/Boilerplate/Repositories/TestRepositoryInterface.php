<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories;

interface TestRepositoryInterface
{
    /**
     * @return bool
     */
    public function nonExistingMethod(): bool;
}
