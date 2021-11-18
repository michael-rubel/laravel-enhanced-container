<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

use MichaelRubel\EnhancedContainer\Core\CallProxy;

class BoilerplateServiceResolvesContextualInMethod implements BoilerplateInterface
{
    /**
     * @return CallProxy
     */
    public function hasContextual(): CallProxy
    {
        return call(BoilerplateInterface::class);
    }
}
