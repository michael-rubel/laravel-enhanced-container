# Laravel Container Calls

[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-container-calls.svg?style=flat-square)](https://packagist.org/packages/michael-rubel/laravel-container-calls)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-container-calls/run-tests?label=tests)](https://github.com/michael-rubel/laravel-container-calls/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-container-calls/Check%20&%20fix%20styling?label=code%20style)](https://github.com/michael-rubel/laravel-container-calls/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/michael-rubel/laravel-container-calls.svg?style=flat-square)](https://packagist.org/packages/michael-rubel/laravel-container-calls)

[![Laravel Version](https://img.shields.io/badge/Laravel-8.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php)](https://php.net)

This package provides syntax sugar for Laravel container calls.

## Installation

You can install the package via composer:

```bash
composer require michael-rubel/laravel-container-calls
```

## Usage
Assume that is your function in the service class:
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
    fn ($service, $app) => $service->yourMethod(100) + 1
);
```

#### The result next call: 101

Note: if you rely on interfaces, the call will automatically resolve bound implementation for you, no need to do it manually.

### ToDo:
- method forwarding;
- call parameters intercepting in bindMethod;

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
