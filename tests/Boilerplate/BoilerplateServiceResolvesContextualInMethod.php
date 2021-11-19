<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

use MichaelRubel\EnhancedContainer\Core\CallProxy;

class BoilerplateServiceResolvesContextualInMethod implements BoilerplateInterface
{
    /**
     * @param BoilerplateInterface $boilerplate
     */
    public function __construct(protected BoilerplateInterface $boilerplate)
    {
    }

    /**
     * @return object
     */
    public function constructorHasContextual(): object
    {
        return $this->boilerplate;
    }

    /**
     * @return CallProxy
     */
    public function methodHasContextual(): CallProxy
    {
        return call(BoilerplateInterface::class, [], static::class);
    }

    /**
     * @return CallProxy
     */
    public function methodHasContextual2(): CallProxy
    {
        return call(BoilerplateInterface::class, [], static::class);
    }
}
