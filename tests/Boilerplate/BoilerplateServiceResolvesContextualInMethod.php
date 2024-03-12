<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

use MichaelRubel\EnhancedContainer\Core\CallProxy;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Repositories\TestRepository;

class BoilerplateServiceResolvesContextualInMethod implements BoilerplateInterface
{
    public function __construct(protected BoilerplateInterface $boilerplate)
    {
    }

    public function constructorHasContextual(): object
    {
        return $this->boilerplate;
    }

    public function methodHasContextual(): CallProxy
    {
        return call(BoilerplateInterface::class, [], static::class);
    }

    public function methodHasContextual2(): CallProxy
    {
        return call(BoilerplateInterface::class, [], static::class);
    }

    public function methodHasContextual3(): CallProxy
    {
        return call(BoilerplateInterface::class, [], TestRepository::class);
    }

    public function methodHasGlobal(): CallProxy
    {
        return call(BoilerplateInterface::class);
    }
}
