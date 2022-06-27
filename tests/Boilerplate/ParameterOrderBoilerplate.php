<?php

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

class ParameterOrderBoilerplate
{
    /**
     * @param  string  $first
     * @param  string  $second
     * @param  string  $third
     *
     * @return string
     */
    public function handle(string $first = '1', string $second = '2', string $third = '3'): string
    {
        return $first . $second . $third;
    }

    /**
     * @param  string  $first
     * @param  string  $second
     *
     * @return string
     */
    public function handleTwo(string $first = '1', string $second = '2'): string
    {
        return $first . $second;
    }

    /**
     * @param  array  $data
     *
     * @return array
     */
    public function getData(array $data): array
    {
        return $data;
    }

    /**
     * @param  string  $test
     * @param  string  $next
     *
     * @return string
     */
    public function getString(string $test, string $next): string
    {
        return $test . $next;
    }
}
