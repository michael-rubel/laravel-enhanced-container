<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class BoilerplateServiceWithVariadicConstructor
{
    private mixed $boilerplates;

    public function __construct(
        BoilerplateInterface ...$boilerplates
    ) {
        $this->boilerplates = $boilerplates;
    }

    public function test(): mixed
    {
        return $this->boilerplates;
    }
}
