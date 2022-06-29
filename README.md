![Laravel Enhanced Container](https://user-images.githubusercontent.com/37669560/176382494-e20d9c49-b4d0-4b0d-a72e-4e82ffb3bd37.png)

# Laravel Enhanced Container
[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-enhanced-container.svg?style=flat-square)](https://packagist.org/packages/michael-rubel/laravel-enhanced-container)
[![Total Downloads](https://img.shields.io/packagist/dt/michael-rubel/laravel-enhanced-container.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-enhanced-container)
[![Code Quality](https://img.shields.io/scrutinizer/quality/g/michael-rubel/laravel-enhanced-container.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-container/?branch=main)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/michael-rubel/laravel-enhanced-container.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-container/?branch=main)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/run-tests/main?style=flat-square&label=tests&logo=github)](https://github.com/michael-rubel/laravel-enhanced-container/actions)
[![PHPStan](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-container/phpstan/main?style=flat-square&label=larastan&logo=laravel)](https://github.com/michael-rubel/laravel-enhanced-container/actions)

> Improved Laravel Service Container features. This package provides enhanced contextual binding, method binding, method forwarding, and syntax sugar to operate on the container. The bindings are defined in a new "fluent" way.

The package requires PHP `^8.x` and Laravel `^8.71` or `^9.0`.

## #StandWithUkraine
[![SWUbanner](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)

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

Install the package via composer:
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

```php
bind(Service::class)->itself();
```

As a singleton:
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
bind(ServiceInterface::class)->instance(new Service());
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

As a variadic dependency:
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

As a primitive:
```php
bind('$param')
   ->contextual(true)
   ->for(ClassWithTypeHintedPrimitive::class);
```

### Contextual binding resolution outside of constructor

```php
call(class: ServiceInterface::class, context: static::class);

// The call automatically resolves the implementation from an interface you passed.
// If you pass context, proxy tries to resolve contextual binding instead of global one first.
// Instead of static::class you may pass any class context for this particular abstract type.
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

Call your service method through container:
```php
call(ServiceInterface::class)->yourMethod(100);
```

Override method behavior in any place of your app.
You can add conditions in your method binding by intercepting parameters.

For example in `tests`:
```php
bind(ApiGatewayContract::class)->to(InternalApiGateway::class);
bind(ApiGatewayContract::class)->method(
    'performRequest',
    fn () => true
);

$apiGateway = call(ApiGatewayContract::class);

$request = $apiGateway->performRequest();

$this->assertTrue($request);
```

Another example from the real-world app:
```php
//
// 🧪 In tests:
//
function testData(array $params): Collection
{
    return collect([
        'object'      => 'payment_intent',
        'amount'      => $params['data']->money->getAmount(),
        'description' => $params['data']->description,
         ...
    ]);
}

bind(StripePaymentProvider::class)->method()->charge(
    fn ($service, $app, $params) => new Payment(
        tap(new PaymentIntent('test_id'), function ($intent) use ($params) {
            testData($params)->each(fn ($value, $key) => $intent->offsetSet($key, $value));
        })
    )
);

//
// ⚙️ In the service class:
//
$data = new StripePaymentData(
    // DTO parameters.
);

call(StripePaymentProvider::class)->charge($data);
// The data bound to the method from `testData` wrapped into PaymentIntent
// object with arguments you passed to the real function call. 🔥
```

Remember that you need to use `call()` to method binding to work. It returns the instance of `CallProxy`.
If you rely on interfaces, proxy will automatically resolve bound implementation for you.

#### Note for package creators
If you want to use method binding in your own package, you need to make sure the [`LecServiceProvider`](https://github.com/michael-rubel/laravel-enhanced-container/blob/main/src/LecServiceProvider.php) registered before you use this feature.
```php
$this->app->register(LecServiceProvider::class);
```

[🔝 back to contents](#contents)

### Method forwarding
This feature automatically forwards the method when it doesn't exist in your base class to another class, if the namespace/classname structure is met. You can enable this feature in the config.

Usual use case: if you have some kind of `Service` or `Domain`, which contains business or application logic, then some kind of `Repository` or `Builder`, which contains your database queries, but you don't want your controllers (or `View/Livewire` components) to be dependent on the repositories directly, and don't want to write the "proxy" methods in the `Service` that references the `Repository` when it comes to fetching the data without any additional operations. The same applies if you don't know where to place the method (will it be only a database query, or might contain additional business logic).

Turn `forwarding_enabled` option on and set the class names that fit your application structure.

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

## Testing

```bash
composer test
```

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
