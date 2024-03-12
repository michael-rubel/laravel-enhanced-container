<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

use MichaelRubel\EnhancedContainer\Traits\BootsCallProxies;

class BoilerplateWithBootedCallProxyWrongParams implements BoilerplateInterface
{
    use BootsCallProxies;

    public function __construct(
        private string $test,
        private bool $boolean,
        private BoilerplateInterface $boilerplateService
    ) {
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

    public function getOriginal(): object
    {
        return $this->boilerplateService;
    }
}
