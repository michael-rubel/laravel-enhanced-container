<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall\Tests\Boilerplate;

class BoilerplateService
{
    /**
     * @param string $first
     * @param int    $second
     *
     * @return bool
     */
    public function test(string $first = '', int $second = 0): bool
    {
        return is_string($first) && is_int($second);
    }

    /**
     * @param int $count
     *
     * @return int
     */
    public function yourMethod(int $count): int
    {
        return $count;
    }
}