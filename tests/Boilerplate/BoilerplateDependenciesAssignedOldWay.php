<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

use MichaelRubel\EnhancedContainer\Traits\BootsCallProxies;

class BoilerplateDependenciesAssignedOldWay implements BoilerplateInterface
{
    use BootsCallProxies;

    /**
     * @var BoilerplateInterface|null
     */
    private ?BoilerplateInterface $boilerplateService = null;

    /**
     * @param  BoilerplateInterface  $boilerplateService
     */
    public function __construct(BoilerplateInterface $boilerplateService)
    {
        // skip assigning dependency

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
}
