<?php

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

use MichaelRubel\EnhancedContainer\Call;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Domain\Best\BestDomain;
use MichaelRubel\EnhancedContainer\Traits\BootsCallProxies;

class BoilerplateServiceWithBootedCallProxyWithMethod
{
    use BootsCallProxies;

    /**
     * Boot the proxy dependencies from handle method.
     */
    public function __construct()
    {
        $this->bootCallProxies('handle');
    }

    /**
     * @param BestDomain $bestDomain
     *
     * @return BestDomain
     */
    public function handle(BestDomain $bestDomain): BestDomain
    {
        return $this->proxy->bestDomain->getInternal(Call::INSTANCE);
    }
}
