<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class BoilerplateServiceWithVariadicConstructor
{
    /**
     * @var mixed
     */
    private mixed $boilerplates;

    /**
     * @param  BoilerplateInterface  ...$boilerplates
     */
    public function __construct(
        BoilerplateInterface ...$boilerplates
    ) {
        $this->boilerplates = $boilerplates;
    }

    /**
     * @return mixed
     */
    public function test(): mixed
    {
        return $this->boilerplates;
    }
}
