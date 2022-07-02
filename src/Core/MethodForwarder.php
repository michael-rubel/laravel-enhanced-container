<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

class MethodForwarder
{
    /**
     * @param  string  $class
     */
    public function __construct(public string $class)
    {
        //
    }

    /**
     * @param  string  $class
     *
     * @return static
     */
    public static function from(string $class): static
    {
        return new static($class);
    }

    /**
     * @param  string|array  $destination
     *
     * @return $this
     */
    public function to(string|array $destination): static
    {
        app()->singleton($this->class . 'forwardsTo', fn () => $destination);

        return $this;
    }
}
