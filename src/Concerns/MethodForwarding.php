<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Concerns;

interface MethodForwarding
{
    public const CLASS_SEPARATOR = '\\';

    /**
     * Forward the method.
     *
     * @return object
     */
    public function forward(): object;

    /**
     * Parse the class where to forward the call.
     *
     * @return string
     */
    public function forwardsTo(): string;
}
