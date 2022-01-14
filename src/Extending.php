<?php

namespace MichaelRubel\EnhancedContainer;

interface Extending
{
    /**
     * Extend the abstract type.
     *
     * @param \Closure $closure
     *
     * @return self
     */
    public function extend(\Closure $closure): self;
}
