<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class BoilerplateServiceWithConstructorClass
{
    /**
     * @param  BoilerplateInterface  $boilerplateService
     */
    public function __construct(
        private BoilerplateInterface $boilerplateService
    ) {
    }

    /**
     * @return object
     */
    public function test(): object
    {
        return $this->boilerplateService;
    }
}
