<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

class Forwarding
{
    public const CONTAINER_KEY = '_forwarder';

    /**
     * @var string
     */
    public string $pendingClass;

    /**
     * @return static
     */
    public static function enable(): static
    {
        return new static();
    }

    /**
     * @param  string  $class
     *
     * @return $this
     */
    public function from(string $class): static
    {
        $this->pendingClass = $class;

        return $this;
    }

    /**
     * @param  string|array  $destination
     *
     * @return $this
     */
    public function to(string|array $destination): static
    {
        app()->singleton($this->pendingClass . static::CONTAINER_KEY, fn () => $destination);

        return $this;
    }
}
