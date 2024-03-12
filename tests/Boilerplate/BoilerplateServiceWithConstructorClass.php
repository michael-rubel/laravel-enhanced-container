<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class BoilerplateServiceWithConstructorClass
{
    public function __construct(
        private BoilerplateInterface $boilerplateService
    ) {
    }

    public function test(): object
    {
        return $this->boilerplateService;
    }
}
