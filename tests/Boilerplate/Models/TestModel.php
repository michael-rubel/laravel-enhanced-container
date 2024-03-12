<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate\Models;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    public bool $nonInServiceExistingProperty = true;

    public function nonExistingInRepositoryMethod(): bool
    {
        return true;
    }
}
