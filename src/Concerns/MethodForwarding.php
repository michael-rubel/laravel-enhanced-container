<?php

declare(strict_types=1);

namespace MichaelRubel\ContainerCall\Concerns;

interface MethodForwarding
{
    /**
     * Forward the method.
     *
     * @return object
     */
    public function forward(): object;
}
