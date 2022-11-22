![Laravel Enhanced Container](https://user-images.githubusercontent.com/37669560/176382494-e20d9c49-b4d0-4b0d-a72e-4e82ffb3bd37.png)

# Laravel Enhanced Container
[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-enhanced-container.svg?style=flat-square)](https://packagist.org/packages/michael-rubel/laravel-enhanced-container)
[![Total Downloads](https://img.shields.io/packagist/dt/michael-rubel/laravel-enhanced-container.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-enhanced-container)
[![Code Quality](https://img.shields.io/scrutinizer/quality/g/michael-rubel/laravel-enhanced-container.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-container/?branch=main)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/michael-rubel/laravel-enhanced-container.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-container/?branch=main)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/run-tests/main?style=flat-square&label=tests&logo=github)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![PHPStan](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/phpstan/main?style=flat-square&label=larastan&logo=laravel)](https://github.com/michael-rubel/laravel-enhanced-container/actions)

This package provides DX tweaks for Service Container in Laravel.

The package requires PHP `8.x` and Laravel `9.x`.

## #StandWithUkraine
[![SWUbanner](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)

## Contents
  * [Installation](#installation)
  * [Usage](#usage)
    + [Resolve contextual binding outside of constructor](#resolve-contextual-binding-outside-of-constructor)
    + [Method binding](#method-binding)
    + [Method forwarding](#method-forwarding)
  * [Testing](#testing)

## Installation

Install the package via composer:
```bash
composer require michael-rubel/laravel-enhanced-container
```

## Usage

### Resolve contextual binding outside of constructor

```php
call(ServiceInterface::class, context: static::class);

// The `call` method automatically resolves the implementation from the interface you passed.
// If you pass the context, the proxy tries to resolve contextual binding instead of global binding first.
```

[🔝 back to contents](#contents)

### Method binding
This feature makes it possible to override the behavior of methods accessed using the `call`.

Assuming that is your function in the service class:
```php
class Service
{
    public function yourMethod(int $count): int
    {
        return $count;
    }
}
```

Bind the service to an interface:
```php
$this->app->bind(ServiceInterface::class, Service::class);
```

Call your service method through container:
```php
call(ServiceInterface::class)->yourMethod(100);
```

For example in feature tests:
```php
$this->app->bind(ApiGatewayContract::class, InternalApiGateway::class);

bind(ApiGatewayContract::class)->method('performRequest', function ($service, $app, $params) {
    // Note: you can access `$params` passed to the method call.

    return true;
});

$apiGateway = call(ApiGatewayContract::class);
$request = $apiGateway->performRequest();
$this->assertTrue($request);
```

Note: if you rely on interfaces, the proxy will automatically resolve bound implementation for you.

#### Note for package creators
If you want to use method binding in your own package, you need to make sure the [`LecServiceProvider`](https://github.com/michael-rubel/laravel-enhanced-container/blob/main/src/LecServiceProvider.php) registered before you use this feature.
```php
$this->app->register(LecServiceProvider::class);
```

[🔝 back to contents](#contents)

### Method forwarding
This feature automatically forwards the method if it doesn't exist in your class to the second one defined in the forwarding configuration.

You can define forwarding in your ServiceProvider:
```php
use MichaelRubel\EnhancedContainer\Core\Forwarding;

Forwarding::enable()
    ->from(Service::class)
    ->to(Repository::class);
```

You can as well use chained forwarding:
```php
Forwarding::enable()
    ->from(Service::class)
    ->to(Repository::class)
    ->from(Repository::class)
    ->to(Model::class);
```

#### Important notes
- Pay attention to which internal instance you're now working on in `CallProxy` when using forwarding. The instance may change without your awareness. If you interact with the same methods/properties on a different instance, the `InstanceInteractionException` will be thrown.
- If you use `PHPStan/Larastan` you'll need to add the `@method` docblock to the service to make it static-analyzable, otherwise it will return an error that the method doesn't exist in the class.

[🔝 back to contents](#contents)

## Testing

```bash
composer test
```

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
