<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Core;

class Forwarding
{
    public const CONTAINER_KEY = '_forwarding';

    /**
     * @var string
     */
    public string $pendingClass;

    /**
     * Initialize the forwarding.
     *
     * @return self
     */
    public static function enable(): self
    {
        return new self();
    }

    /**
     * @param  string  $class
     *
     * @return $this
     */
    public function from(string $class): static
    {
        $this->pendingClass = $this->resolve($class);

        return $this;
    }

    /**
     * @param  string  $destination
     *
     * @return $this
     */
    public function to(string $destination): static
    {
        app()->singleton(
            $this->pendingClass . static::CONTAINER_KEY,
            $this->resolve($destination)
        );

        return $this;
    }

    /**
     * @param  string  $class
     *
     * @return string
     */
    protected function resolve(string $class): string
    {
        return ! interface_exists($class) ? $class : app($class)::class;
    }
}
