<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class BoilerplateServiceWithConstructor implements BoilerplateInterface
{
    public function __construct(
        private bool $param
    ) {
    }

    public function test(string $first = '', int $second = 0): bool
    {
        return is_string($first) && is_int($second);
    }

    public function yourMethod(int $count): int
    {
        if ($this->param) {
            return $count + 1;
        }

        return $count;
    }

    public function getParam(): bool
    {
        return $this->param;
    }
}
