# php-router

A PHP library to enable mapping of URLs that do not exist.

*Project under development.*

## Installation

composer require antoniokadid/php-router:dev-master

## Requirements

* PHP 7.1 or above.
* mod_rewrite must be enabled

## Configuration
*.htaccess configuration required to redirect all requests to a single PHP file that contains the route definitions.*

```apacheconfig
# example for .htacccess configuration
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-l
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [QSA,L,END]
```

## Examples

GET request *en/hello/test*

URL keywords are defined with colon (:).

```php
use AntonioKadid\Router\Router;

Router::get(':language/:controller/:action')
    ->then(function ($language, $controller, $action) {
        echo nl2br(sprintf("%s\n%s\n%s", $language, $controller, $action));
    });

Router::execute();

/**
 * URL: en/hello/test
 *
 * Output:
 *   en
 *   hello
 *   test
 */
```

GET request *en/hello/test* with callback parameters in different order with URL keywords.

Router automatically matches the names of the parameters to the url keywords.

```php
use AntonioKadid\Router\Router;

Router::get(':language/:controller/:action')
    ->then(function ($action, $controller, $language) {
        echo nl2br(sprintf("%s\n%s\n%s", $language, $controller, $action));
    });

Router::execute();

/**
 * URL: en/hello/test
 *
 * Output:
 *   en
 *   hello
 *   test
 */
```

The above example using a class instead of callback.

```php
use AntonioKadid\Router\Router;

class HelloController
{
    public static function handle($language, $controller, $action)
    {
        echo nl2br(sprintf("%s\n%s\n%s", $language, $controller, $action));
    }
}

Router::get(':language/:controller/:action')
    ->then(['HelloController', 'handle']);

Router::execute();

/**
 * URL: en/hello/test
 *
 * Output:
 *   en
 *   hello
 *   test
 */
```

Similar to the above example invoke injection handler for language.

```php
use AntonioKadid\Router\Router;

class Language
{
    private $language;

    public function __construct(string $language)
    {
        $this->language = $language;
    }

    public function getCode(): string
    {
        return $this->language;
    }
}

class HelloController
{
    public static function handle(Language $language, $controller, $action)
    {
        echo nl2br(sprintf("%s\n%s\n%s", $language->getCode(), $controller, $action));
    }
}

Router::get(':language/:controller/:action')
    ->then(['HelloController', 'handle']);

Router::execute();

/**
 * URL: en/hello/test
 *
 * Output:
 *   en
 *   hello
 *   test
 */
```

Conditional route execution.

```php
use AntonioKadid\Router\Router;

// This route will be executed if language has value 'en' or 'el' and if controller has value 'hello'.
Router::get(':language/:controller/:action')
    ->if([
        'language' => '/^en|el$/i',
        'controller' => function($controller) {
            return strcasecmp($controller, 'hello') === 0;
        }
    ])
    ->then(function($language, $controller, $action) {
        return nl2br(sprintf("%s\n%s\n%s", $language, $controller, $action));
    });

$returnedRouteValue = Router::execute();

echo $returnedRouteValue;

/**
 * URL: en/hello/test
 *
 * Output:
 *   en
 *   hello
 *   test
 */
```
## LICENSE

php-router is released under MIT license.
