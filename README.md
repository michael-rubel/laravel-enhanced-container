![laravel-lec](https://user-images.githubusercontent.com/37669560/135411590-5b67ccef-fcc5-4bd6-b7c8-e4b9aa659cb8.png)

# Laravel Enhanced Container
[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-enhanced-container.svg?style=flat-square)](https://packagist.org/packages/michael-rubel/laravel-enhanced-container)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/run-tests?label=Tests)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![PHPStan](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/phpstan?label=Larastan)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-container/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-container/?branch=main)
[![Psalm](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/psalm?label=Psalm)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![PHP CS Fixer](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/check%20&%20fix%20styling%20with%20php-codesniffer?label=CodeSniffer)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![PHP CodeSniffer](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/check%20&%20fix%20styling%20with%20php-cs-fixer?label=CS-Fixer)](https://github.com/michael-rubel/laravel-enhanced-container/actions)

This package provides syntax sugar for the Laravel container calls and bindings, automatic resolution of bound implementation, method forwarding, and an enhanced version of the Laravel method binding feature.

The package requires PHP 8.0 and Laravel 8.x.
Support for future versions of PHP & Laravel will be provided.

## Installation

You can install the package via composer:

```bash
composer require michael-rubel/laravel-enhanced-container
```

## Usage

### Basic binding with new syntax
```php
bind(ServiceInterface::class)->to(Service::class);

bind(Service::class)->itself();
```

As singleton:
```php
bind(ServiceInterface::class)->singleton(Service::class);

singleton(Service::class);
```

As scoped instance:
```php
bind(ServiceInterface::class)->scoped(Service::class);

scoped(Service::class);
```

### Contextual binding with new syntax
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

### Contextual method binding with input parameter interception
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

$service = call(ServiceInterface::class);

$call = $service->externalApiRequestReturnsFalse();

$this->assertFalse($call);
```

Remember that you need to use `call()` to method binding to work. It returns the instance of `CallProxy`.
If you rely on interfaces, the proxy will automatically resolve bound implementation for you, no need to do it manually.

Optionally, if you want to easily wrap all your class constructor's dependencies to `CallProxy`, you can use `BootsCallProxies` trait and then call `$this->bootCallProxies()` in your constructor. It will bootstrap the `proxy` class property that utilizes Laravel's native `Fluent` object. What it would look like:

```php
use MichaelRubel\EnhancedContainer\Traits\BootsCallProxies;

class AnyYourClass
{
    use BootsCallProxies;

    /**
     * @param ServiceInterface $service
     */
    public function __construct(
        private ServiceInterface $service
    ) {
        $this->bootCallProxies();
    }

    /**
     * @return object
     */
    public function getProxiedClass(): object
    {
        return $this->proxy->service; // your proxied service
    }

    /**
     * @return object
     */
    public function getOriginal(): object
    {
        return $this->service; // your original is still available
    }
}
```


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
