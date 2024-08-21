<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class BoilerplateServiceWithConstructorPrimitive implements BoilerplateInterface
{
    public function __construct(
        private bool $param,
        private string $nextParam
    ) {}

    public function getParam(): bool
    {
        return $this->param;
    }

    public function getNextParam(): string
    {
        return $this->nextParam;
    }
}
