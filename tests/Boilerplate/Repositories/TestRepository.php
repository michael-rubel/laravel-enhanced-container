<?php

namespace MichaelRubel\ContainerCall\Tests\Boilerplate\Repositories;

class TestRepository implements TestRepositoryInterface
{
    /**
     * @return bool
     */
    public function nonExistingMethod(): bool
    {
        return true;
    }
}
