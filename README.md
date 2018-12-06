# php-router

A PHP library to enable mapping of URLs that do not exist.

*Project under development.*

## Installation

composer require antoniokadid/php-router

## Configuration
*.htaccess configuration required to redirect all requests to a single PHP file that contains the route definitions.*

*Depends on mod_rewrite.*

```apacheconfig
# example for .htacccess configuration
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-l
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [QSA,L,END]
```

## Examples

```php
use Router\Router;

Router::register('GET', ':language/:controller/:action', function ($language, $controller, $action) {
    echo nl2br(sprintf("%s\n%s\n%s", $language, $controller, $action));
});

Router::handle();

/**

URL: en/hello/test

Output:
    en
    hello
    test
*/
```

## LICENSE

php-router is released under MIT licence.