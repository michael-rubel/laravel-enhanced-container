<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

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
}
