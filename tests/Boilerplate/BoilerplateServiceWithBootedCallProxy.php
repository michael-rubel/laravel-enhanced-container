<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Domain\Best\BestDomain;
use MichaelRubel\EnhancedContainer\Traits\BootsCallProxies;

class BoilerplateServiceWithBootedCallProxy implements BoilerplateInterface
{
    use BootsCallProxies;

    /**
     * @param BoilerplateInterface $boilerplateService
     */
    public function __construct(
        private BoilerplateInterface $boilerplateService
    ) {
        $this->bootCallProxies();
    }

    /**
     * @return object
     */
    public function getProxy(): object
    {
        return $this->proxy;
    }

    /**
     * @return object
     */
    public function getProxiedClass(): object
    {
        return $this->proxy->boilerplateService;
    }

    /**
     * @return object
     */
    public function getOriginal(): object
    {
        return $this->boilerplateService;
    }

    /**
     * @param BestDomain $bestDomain
     *
     * @return BestDomain
     */
    public function handle(BestDomain $bestDomain): BestDomain
    {
        $this->bootCallProxies('handle');

        return $this->proxy->bestDomain->getInternal('instance');
    }
}
