<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class BoilerplateService implements BoilerplateInterface
{
    public bool $testProperty = true;

    public function test(string $first = '', int $second = 0): bool
    {
        return is_string($first) && is_int($second);
    }

    public function yourMethod(int $count): int
    {
        return $count;
    }
}
