<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Concerns;

interface Forwarding
{
    public const CLASS_SEPARATOR = '\\';

    /**
     * Resolve the class where we want to forward.
     *
     * @return object
     */
    public function resolveClass(): object;

    /**
     * Parse the class where to forward the call.
     *
     * @return string
     */
    public function forwardsTo(): string;
}
