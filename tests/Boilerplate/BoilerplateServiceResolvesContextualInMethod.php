<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

use MichaelRubel\EnhancedContainer\Core\CallProxy;

class BoilerplateServiceResolvesContextualInMethod implements BoilerplateInterface
{
    /**
     * @return CallProxy
     */
    public function methodHasContextual(): CallProxy
    {
        return call(BoilerplateInterface::class);
    }

    /**
     * @return CallProxy
     */
    public function methodHasContextual2(): CallProxy
    {
        return call(BoilerplateInterface::class);
    }
}
