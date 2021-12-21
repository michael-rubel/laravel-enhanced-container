<?php

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class ParameterOrderBoilerplate
{
    public function handle(string $first = '1', string $second = '2', string $third = '3'): string
    {
        return $first . $second . $third;
    }
}
