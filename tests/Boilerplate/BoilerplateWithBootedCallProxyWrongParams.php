<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedContainer\Tests\Boilerplate;

use MichaelRubel\EnhancedContainer\Traits\BootsCallProxies;

class BoilerplateWithBootedCallProxyWrongParams implements BoilerplateInterface
{
    use BootsCallProxies;

    /**
     * @param  string  $test
     * @param  bool  $boolean
     * @param  BoilerplateInterface  $boilerplateService
     */
    public function __construct(
        private string $test,
        private bool $boolean,
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
