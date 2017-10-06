# Laravel Sentry

#### This is not an integration of [Sentry](https://sentry.io/welcome/).
This is a Laravel package for recording visitor on your articles, project or any page in your application. It will save on the database the visitor/user who view page on your app with their details like location, ip address, browser, machine etc.


### Installation

- Install the `goper-leo/laravel-sentry`:
```bash
composer require goper-leo/laravel-sentry
```
or add to your `composer.json`
```
"goper-leo/laravel-sentry": "dev-master"
```
and run `composer update`

- Add the configuration for the package run `php artisan vendor:publish --provider=SentryServiceProvider`

- After vendor publish create the table on your database run `php artisan migrate` the table will be named *sentries*

> If you're on Laravel 5.4 or earlier, you'll need to add the following to your `config/app/php`
```php
'providers' => array(
    EETechMedia\Sentry\SentryServiceProvider::class,
)
'aliases' => array(
    'Sentry' => EETechMedia\Sentry\SentryFacade::class,
)
```

### Usage

Add the facade on your controller
```
use Sentry;
```
Sentry columns
- base_id = Your point or source this only allow integer value - this could be `page id` , `project_id` or any point. This can be **null**.

- url = If you don't use `base_id` use url as your point / primary key. This is string value, this can be **null** also.

> ***Note:*** `base_id` and `url` cannot be both null, there must be at least one of the both will have a value. To make your primary key or point of reference.

To add user/viewer simply put on your controller
```
$viewer = [
    'base_id' => 'point_id',
    'url' => 'lorem-ipsum'
];

Sentry::plant($viewer);
```

To fetch / get data of all viewers
```
Sentry::getAll();
or
Sentry::getAll('base_id-or-url'); // To get all records accoring to condition
```

Other methods
- getWhere
- getObserverSpot
- getObserverHeaders
