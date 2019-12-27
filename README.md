```
1.
composer install laravel-utilities

2.
config/app.php:
    ...
    'providers' => [
        ...
        Naveed\Utils\UtilsServiceProvider::class,
        ...

3.
php artisan vendor:publish --provider="Naveed\Utils\UtilsServiceProvider"


```
