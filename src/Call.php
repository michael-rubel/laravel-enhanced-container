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
    public const PREVIOUS = 'previous';

    /**
     * @const
     */
    public const FORWARDING = 'forwarding';

    /**
     * @const
     */
    public const INTERACTIONS = 'interactions';

    /**
     * @const
     */
    public const METHOD = '__call';

    /**
     * @const
     */
    public const GET = '__get';

    /**
     * @const
     */
    public const SET = '__set';

    /**
     * @const
     */
    public const ISSET = '__isset';

    /**
     * @const
     */
    public const UNSET = '__unset';

    /**
     * Gets the internal property by name.
     *
     * @param  string  $property
     *
     * @return mixed
     */
    public function getInternal(string $property): mixed;
}
