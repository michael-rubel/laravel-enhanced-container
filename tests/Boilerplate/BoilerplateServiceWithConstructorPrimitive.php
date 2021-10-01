<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class BoilerplateServiceWithConstructorPrimitive implements BoilerplateInterface
{
    /**
     * @param bool $param
     */
    public function __construct(
        private bool $param
    ) {
    }

    /**
     * @return bool
     */
    public function getParam(): bool
    {
        return $this->param;
    }
}
