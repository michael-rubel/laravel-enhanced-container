<?php

namespace MichaelRubel\EnhancedContainer\Tests;

use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateInterface;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateService;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\BoilerplateServiceWithConstructor;
use MichaelRubel\EnhancedContainer\Tests\Boilerplate\Models\TestModel;

class BaseBindingTest extends TestCase
{
    /** @test */
    public function testCanBindAnAbstractToConcrete()
    {
        bind(BoilerplateInterface::class)->to(BoilerplateService::class);

        app()->bound(BoilerplateInterface::class);

        $instance = resolve(BoilerplateInterface::class);

        $this->assertInstanceOf(BoilerplateService::class, $instance);
    }

    /** @test */
    public function testCanBindAnAbstractToConcreteAsSingleton()
    {
        bind(BoilerplateInterface::class)->to(BoilerplateService::class, true);

        app()->bound(BoilerplateInterface::class);

        $instance = resolve(BoilerplateInterface::class);

        $this->assertInstanceOf(BoilerplateService::class, $instance);
    }

    /** @test */
    public function testCanBindAnAbstractToConcreteAsSingletonWithAnotherSyntax()
    {
        bind(BoilerplateInterface::class)->singleton(BoilerplateService::class);

        app()->bound(BoilerplateInterface::class);

        $instance = resolve(BoilerplateInterface::class);

        $this->assertInstanceOf(BoilerplateService::class, $instance);
    }

    /** @test */
    public function testCanBindAnAbstractToConcreteAsScopedInstance()
    {
        bind(BoilerplateInterface::class)->scoped(BoilerplateService::class);

        app()->bound(BoilerplateInterface::class);

        $instance = resolve(BoilerplateInterface::class);

        $this->assertInstanceOf(BoilerplateService::class, $instance);
    }

    /** @test */
    public function testCanSetInstanceToTheContainer()
    {
        bind(BoilerplateInterface::class)->to(BoilerplateService::class);
        bind(BoilerplateInterface::class)->instance(
            new BoilerplateServiceWithConstructor(true)
        );

        $this->assertInstanceOf(
            BoilerplateServiceWithConstructor::class,
            resolve(BoilerplateInterface::class)
        );
    }

    /** @test */
    public function testWrapsObjectsToClosure()
    {
        $model = app(TestModel::class);
        bind('test')->to($model);
        $this->assertSame($model, app('test'));

        $model = app(TestModel::class);
        bind('test')->singleton($model);
        $this->assertSame($model, app('test'));

        $model = app(TestModel::class);
        bind('test')->scoped($model);
        $this->assertSame($model, app('test'));

        bind('closure')->to(fn () => 'content');
        $this->assertEquals('content', app('closure'));

        bind('string')->to(TestModel::class);
        $this->assertEquals($model, app('string'));
    }
}
