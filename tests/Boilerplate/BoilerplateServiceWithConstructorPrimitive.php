<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class BoilerplateServiceWithConstructorPrimitive implements BoilerplateInterface
{
    /**
     * @param  bool  $param
     * @param  string  $nextParam
     */
    public function __construct(
        private bool $param,
        private string $nextParam
    ) {
    }

    /**
     * @return bool
     */
    public function getParam(): bool
    {
        return $this->param;
    }

    /**
     * @return string
     */
    public function getNextParam(): string
    {
        return $this->nextParam;
    }
}
