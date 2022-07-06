<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Models;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    /**
     * @var bool
     */
    public bool $nonInServiceExistingProperty = true;

    /**
     * @return bool
     */
    public function nonExistingInRepositoryMethod(): bool
    {
        return true;
    }
}
