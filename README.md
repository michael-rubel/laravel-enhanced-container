![laravel-lec](https://user-images.githubusercontent.com/37669560/135411590-5b67ccef-fcc5-4bd6-b7c8-e4b9aa659cb8.png)

# Laravel Enhanced Container
[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-enhanced-container.svg?style=flat-square)](https://packagist.org/packages/michael-rubel/laravel-enhanced-container)
[![Total Downloads](https://img.shields.io/packagist/dt/michael-rubel/laravel-enhanced-container.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-enhanced-container)
[![Code Quality](https://img.shields.io/scrutinizer/quality/g/michael-rubel/laravel-enhanced-container.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-container/?branch=main)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/michael-rubel/laravel-enhanced-container.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-container/?branch=main)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/run-tests/main?style=flat-square&label=tests&logo=github)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![PHPStan](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/phpstan/main?style=flat-square&label=larastan&logo=laravel)](https://github.com/michael-rubel/laravel-enhanced-container/actions)

> Improved Laravel Service Container features. This package provides enhanced contextual binding, method binding, method forwarding, and syntax sugar to operate on the container. The bindings are defined in a new "fluent" way.

The package requires PHP `^8.x` and Laravel `^8.71`.

[![PHP Version](https://img.shields.io/badge/php-^8.x-777BB4?style=flat-square&logo=php)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-^8.71-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![Laravel Octane Compatible](https://img.shields.io/badge/octane-compatible-success?style=flat-square&logo=laravel)](https://github.com/laravel/octane)

## Contents
  * [Installation](#installation)
  * [Usage](#usage)
    + [Basic binding](#basic-binding)
    + [Binding instances](#binding-instances)
    + [Extending bindings](#extending-bindings)
    + [Contextual binding](#contextual-binding)
    + [Contextual binding resolution outside of constructor](#contextual-binding-resolution-outside-of-constructor)
    + [Method binding](#method-binding)
    + [Method forwarding](#method-forwarding)
  * [Testing](#testing-the-package)

## Installation

You can install the package via composer:

### Laravel 9
```bash
composer require michael-rubel/laravel-enhanced-container
```

### Laravel 8
```bash
composer require michael-rubel/laravel-enhanced-container "^6.0"
```

### Config
Publish the config if you want to customize package settings:
```bash
php artisan vendor:publish --tag="enhanced-container-config"
```

## Usage

### Basic binding
```php
bind(ServiceInterface::class)->to(Service::class);
```

Bind just an implementation:
```php
bind(Service::class)->itself();
```

As singleton:
```php
bind(ServiceInterface::class)->singleton(Service::class);
```

```php
singleton(Service::class);
```

As scoped singleton:
```php
bind(ServiceInterface::class)->scoped(Service::class);
```

```php
scoped(Service::class);
```

### Binding instances
```php
bind(ServiceInterface::class)->instance(Service::class);
```

```php
instance(ServiceInterface::class, new Service())
```

### Extending bindings
```php
extend(ServiceInterface::class, function ($service) {
    $service->testProperty = true;

    return $service;
})
```

[🔝 back to contents](#contents)

### Contextual binding
```php
bind(ServiceInterface::class)
   ->contextual(Service::class)
   ->for(ClassWithTypeHintedInterface::class);
```

As variadic dependency:
```php
bind(ServiceInterface::class)
   ->contextual(
       fn ($app) => [
           $app->make(Service::class, ['param' => true]),
           $app->make(AnotherServiceSharingTheSameInterface::class),
       ]
   )
   ->for(ClassWithTypeHintedInterface::class);
```

As primitive:
```php
bind('$param')
   ->contextual(true)
   ->for(ClassWithTypeHintedPrimitive::class);
```

### Contextual binding resolution outside of constructor

```php
call(class: ServiceInterface::class, context: static::class);

// the call automatically resolves the implementation from an interface you passed
// if we're passing context, it tries to resolve contextual binding instead of global one first
// instead of static::class you may pass any class context for this particular abstract type
```

[🔝 back to contents](#contents)

### Method binding
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
bind(ServiceInterface::class)->to(Service::class);
```

You can perform the call to your service through container:
```php
call(ServiceInterface::class)->yourMethod(100);
```

Override method behavior in any place of your app. You can even add conditions in your method binding by intercepting parameters:
```php
bind(ServiceInterface::class)->method('yourMethod', function ($service, $app, $params) {
    if ($params['count'] === 100) {
        return $service->yourMethod($params['count']) + 1;
    }

    return false;
});

call(ServiceInterface::class)->yourMethod(100);

// 101

call(ServiceInterface::class)->yourMethod(200);

// false
```

#### You can easily mock the methods in your tests as well.

For example:
```php
bind(ServiceInterface::class)->to(Service::class);
bind(ServiceInterface::class)->method(
    'externalApiRequestReturnsFalse',
    fn () => false
);

$service = call(ServiceInterface::class);

$call = $service->externalApiRequestReturnsFalse();

$this->assertFalse($call);
```

Remember that you need to use `call()` to method binding to work. It returns the instance of `CallProxy`.
If you rely on interfaces, the proxy will automatically resolve bound implementation for you, no need to do it manually.

[🔝 back to contents](#contents)

### Method forwarding
This feature automatically forwards the method when it doesn't exist in your base class to another class, if the namespace/classname structure is met. You can enable this feature in the config.

Usual use case: if you have some kind of `Service` or `Domain`, which contains business or application logic, then some kind of `Repository` or `Builder`, which contains your database queries, but you don't want your controllers (or `View/Livewire` components) to be dependent on the repositories directly, and don't want to write the "proxy" methods in the `Service` that references the `Repository` when it comes to just fetch the data without any additional operations.

Turn `forwarding_enabled` option on and set the class names that fits your application structure.

Assuming your structure is:
```php
Logic:
- App/Services/Users/UserService
Queries: 
- App/Repositories/Users/UserRepository
```

And your classes:
```php
class UserService
{
    public function someMethod(): bool
    {
        return true;
    }
}

class UserRepository
{
    public function yourUser(): bool
    {
        return true;
    }
}
```

Then call the method to fetch the user:
```php
call(UserService::class)->yourUser();
```

The result will be `true` despite the method is missing in `UserService`.
If you put the same method in the `UserService`, it will fetch the result from the service itself.

- Note: if you use `PHPStan/Larastan` you'll need to add the `@method` docblock to the service to make it static-analyzable, otherwise it will return an error that the method doesn't exist in the class.

If the forwarding in your application is enabled globally but you want to execute something bypassing the scheme, you can run it in disabled mode or vice versa:
```php
runWithoutForwarding(
    fn () => call(Service::class)->yourMethod(100)
);

runWithForwarding(
    fn () => call(Service::class)->yourMethod(150)
);
```

[🔝 back to contents](#contents)

## Testing the package

```bash
composer test
```
