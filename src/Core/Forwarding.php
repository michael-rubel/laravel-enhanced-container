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
        $this->pendingClass = $class;

        return $this;
    }

    /**
     * @param  string  $destination
     *
     * @return $this
     */
    public function to(string $destination): static
    {
        app()->singleton($this->pendingClass . static::CONTAINER_KEY, $destination);

        return $this;
    }
}
