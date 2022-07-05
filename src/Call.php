<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer;

interface Call
{
    /**
     * Constants to use for referring internal properties.
     *
     * @const
     */
    public const INSTANCE = 'instance';

    /**
     * Gets the internal property by name.
     *
     * @param  string  $property
     *
     * @return mixed
     */
    public function getInternal(string $property): mixed;
}
