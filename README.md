# Laravel Enhanced Container

[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-enhanced-container.svg?style=flat-square)](https://packagist.org/packages/michael-rubel/laravel-enhanced-container)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/run-tests?label=tests)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![PHP CS Fixer](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/check%20&%20fix%20styling%20with%20php-codesniffer?label=phpcs)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![PHP CodeSniffer](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/check%20&%20fix%20styling%20with%20php-cs-fixer?label=php-cs-fixer)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![PHPStan](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/phpstan?label=phpstan)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![Psalm](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/psalm?label=psalm)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/michael-rubel/laravel-enhanced-container.svg?style=flat-square)](https://packagist.org/packages/michael-rubel/laravel-enhanced-container)

This package provides syntax sugar for the Laravel container calls and bindings, automatic resolution of bound implementation, method forwarding, and an enhanced version of the Laravel method binding feature.

The package requires PHP 8.0 and Laravel 8.x.
Future versions of PHP and Laravel will be maintained.

## Installation

You can install the package via composer:

```bash
composer require michael-rubel/laravel-enhanced-container
```

## Usage

### Basic binding with new syntax
```php
bind(ServiceInterface::class)->to(Service::class);
```

As singleton:
```php
bind(ServiceInterface::class)->singleton(Service::class);
```

As scoped instance:
```php
bind(ServiceInterface::class)->scoped(Service::class);
```


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

Perform the call to your service through container:
```php
call(Service::class)->yourMethod(100)
```

You can pass it as `$this` or basic class object too:
```php
call(new Service())->yourMethod(100)
```
Then override the method behavior in any place of your app:
```php
bind(Service::class)->method()->yourMethod(function ($service, $app, $params) {
    return $service->yourMethod($params['count']) + 1;
});
```

Alternative syntax:
```php
bind(Service::class)->method('yourMethod', function ($service, $app, $params) {
    return $service->yourMethod($params['count']) + 1;
});
```

#### The result next call: 101

##### You can easily mock the methods in your tests as well, and it counts as code coverage. 😉

For example:
```php
bind(ServiceInterface::class)->to(Service::class);
bind(ServiceInterface::class)->method(
    'externalApiRequestReturnsFalse',
    fn () => false
);

$call = call(ServiceInterface::class)->externalApiRequestReturnsFalse();

$this->assertFalse($call);
```

Remember that you need to use `call()` to method binding to work.
If you rely on interfaces, the call will automatically resolve bound implementation for you, no need to do it manually.

### Method forwarding
This feature automatically forwards the method when it doesn't exist in your base class to another class, if the namespace/classname structure is met.

Usual use case: if you have some kind of `Service` or `Domain`, which contains business or application logic, then some kind of `Repository` or `Builder`, which contains your database queries, but you don't want your controllers (or `View/Livewire` components) to be dependent on the repositories directly, and don't want to write the "proxy" methods in the `Service` that references the `Repository` when it comes to just fetch the data without any additional operations.

To enable this feature, publish the config:
```bash
php artisan vendor:publish --tag="enhanced-container-config"
```

Then turn `forwarding_enabled` option on and set the class names that met your application structure.

Assuming your structure is:
```php
Logic:
- App/Services/Users/UserService
Queries: 
- App/Repositories/Users/UserRepository
```

Then your classes:
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
call(UserService::class)->yourUser()
```

The result will be `true` despite the method is missing in `UserService`.
If you put the same method in the `UserService`, it will fetch the result from the service itself.

- Note: if you use `PHPStan/Larastan` you'll need to add the `@method` docblock to the service to make it static-analyzable, otherwise it will return an error that the method doesn't exist in the class.

## Testing

```bash
composer test
```
