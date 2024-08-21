<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

class Forwarding
{
    public const CONTAINER_KEY = '_forwarding';

    /**
     * @var class-string
     */
    public string $pendingClass;

    /**
     * Initialize the forwarding.
     */
    public static function enable(): self
    {
        return new self;
    }

    /**
     * Define the pending class.
     */
    public function from(string $class): static
    {
        $this->pendingClass = $this->resolve($class);

        return $this;
    }

    /**
     * Define the forwarding for the pending class set previously.
     */
    public function to(string $destination): static
    {
        app()->bind(
            abstract: $this->pendingClass . static::CONTAINER_KEY,
            concrete: $this->resolve($destination)
        );

        return $this;
    }

    /**
     * Extract an implementation from the interface if passed.
     */
    protected function resolve(string $class): string
    {
        return ! interface_exists($class) ? $class : app($class)::class;
    }
}
