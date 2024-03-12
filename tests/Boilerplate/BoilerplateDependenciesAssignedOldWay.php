<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

use MichaelRubel\EnhancedContainer\Traits\BootsCallProxies;

class BoilerplateDependenciesAssignedOldWay implements BoilerplateInterface
{
    use BootsCallProxies;

    private ?BoilerplateInterface $boilerplateService = null;

    public function __construct(BoilerplateInterface $boilerplateService)
    {
        // skip assigning dependency

        $this->bootCallProxies();
    }

    public function getProxy(): object
    {
        return $this->proxy;
    }

    public function getProxiedClass(): object
    {
        return $this->proxy->boilerplateService;
    }
}
