# Laravel Container Calls

[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-container-calls.svg?style=flat-square)](https://packagist.org/packages/michael-rubel/laravel-container-calls)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-container-calls/run-tests?label=tests)](https://github.com/michael-rubel/laravel-container-calls/actions)
[![PHP CS Fixer](https://img.shields.io/github/workflow/status/michael-rubel/laravel-container-calls/check%20&%20fix%20styling%20with%20php-codesniffer?label=phpcs)](https://github.com/michael-rubel/laravel-container-calls/actions)
[![PHP CodeSniffer](https://img.shields.io/github/workflow/status/michael-rubel/laravel-container-calls/check%20&%20fix%20styling%20with%20php-cs-fixer?label=php-cs-fixer)](https://github.com/michael-rubel/laravel-container-calls/actions)
[![PHPStan](https://img.shields.io/github/workflow/status/michael-rubel/laravel-container-calls/phpstan?label=phpstan)](https://github.com/michael-rubel/laravel-container-calls/actions)
[![Psalm](https://img.shields.io/github/workflow/status/michael-rubel/laravel-container-calls/psalm?label=psalm)](https://github.com/michael-rubel/laravel-container-calls/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/michael-rubel/laravel-container-calls.svg?style=flat-square)](https://packagist.org/packages/michael-rubel/laravel-container-calls)

This package provides syntax sugar for the Laravel container calls, automatic interface implementation resolution, method forwarding, and an enhanced version of Laravel's method binding feature.

## Installation

You can install the package via composer:

```bash
composer require michael-rubel/laravel-container-calls
```

## Usage

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
bindMethod(
    Service::class,
    'yourMethod',
    fn ($service, $app, $params) => $service->yourMethod($params['count']) + 1
);
```

#### The result next call: 101

- Note: if you rely on interfaces, the call will automatically resolve bound implementation for you, no need to do it manually.

### Method forwarding
This feature automatically forwards the method that doesn't exist in your class to another class if the namespace structure is met.

Usual use case: if you have some kind of `Service` or `Domain`, which contains business or application logic, then some kind of `Repository` or `Builder`, which contains your database queries, but you don't want your controllers (or `View/Livewire` components) to be dependent on the repositories directly, and don't want to write the "proxy" methods in the `Service` that references the `Repository` when it comes to just fetch the data without any additional operations.

To enable this feature, publish the config and set appropriate names that meet your namespace:
```bash
php artisan vendor:publish --tag="container-calls-config"
```

Assuming your project structure is:
```php
Logic: App/Services/Users/UserService
Queries: App/Repositories/Users/UserRepository
```

Then your methods in classes:
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

- Note: if you use `PHPStan/Larastan` you'll need to add the `@method` docblock to the service to make it static-analyzable, otherwise it will return the error that the method doesn't exist in the class.

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.
