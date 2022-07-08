<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer;

interface Call
{
    /**
     * @const
     */
    public const INSTANCE = 'instance';

    /**
     * @const
     */
    public const STATE = 'state';

    /**
     * @const
     */
    public const METHOD = 'methodCall';

    /**
     * @const
     */
    public const GET = 'getProperty';

    /**
     * @const
     */
    public const SET = 'setProperty';

    /**
     * Gets the internal property by name.
     *
     * @param  string  $property
     *
     * @return mixed
     */
    public function getInternal(string $property): mixed;
}
