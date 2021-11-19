<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

use MichaelRubel\EnhancedContainer\Core\CallProxy;

class BoilerplateServiceResolvesGlobalInMethod implements BoilerplateInterface
{
    /**
     * @return CallProxy
     */
    public function getsGlobalBinding(): CallProxy
    {
        return call(BoilerplateInterface::class, [], static::class);
    }
}
