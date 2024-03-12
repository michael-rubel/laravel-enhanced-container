<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class ParameterOrderBoilerplate
{
    public function handle(string $first = '1', string $second = '2', string $third = '3'): string
    {
        return $first . $second . $third;
    }

    public function handleTwo(string $first = '1', string $second = '2'): string
    {
        return $first . $second;
    }

    public function getData(array $data): array
    {
        return $data;
    }

    public function getString(string $test, string $next): string
    {
        return $test . $next;
    }
}
