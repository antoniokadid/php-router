# WAPPKit Core - HTTP
A PHP library to receive and respond to HTTP requests.

Part of Web Application Kit (WAPPKit) Core which powers WAPPKit, a privately owned CMS.

*Project under development and may be subject to a lot of changes. Use at your own risk.*

## Installation

composer require antoniokadid/wappkit-core-http

## Requirements

* PHP 7.1 or above.
* mod_rewrite must be enabled

## Configuration
*.htaccess configuration required to redirect all requests to a single PHP file that contains the route definitions.*

```apacheconfig
# example for .htaccess configuration
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-l
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [QSA,L,END]
```

## Definitions

- URL keywords are defined with curly brackets ({}).

- Double star (**) indicates that anything past this symbol until the end of URL will be a match.

- Single star (*) indicates that anything past this symbol until next slash (/) will be a match.

## Examples

```php
use AntonioKadid\WAPPKitCore\HTTP\Routing\Router;

/** 
 * Will match any of the following (3 parts separated by 2 slashes)
 *
 * en/admin/home
 * en/test/home
 * fr/page/home
 */
Router::get('{language}/*/{action}')
    ->then(function ($language, $action) {
        echo nl2br(sprintf("%s\n%s", $language, $action));
    });

/**
 * Will match any route that starts with at least something followed by a slash.
 * Should always defined last otherwise it will match any other route defined after it.
 */
Router::get('{language}/**')
    ->then(function ($language) {
        echo $language;
    });

Router::execute();
```



GET request *en/hello/test*

```php
use AntonioKadid\WAPPKitCore\HTTP\Routing\Router;

Router::get('{language}/{controller}/{action}')
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
use AntonioKadid\WAPPKitCore\HTTP\Routing\Router;

Router::get('{language}/{controller}/{action}')
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
use AntonioKadid\WAPPKitCore\HTTP\Routing\Router;

class HelloController
{
    public static function handle($language, $controller, $action)
    {
        echo nl2br(sprintf("%s\n%s\n%s", $language, $controller, $action));
    }
}

Router::get('{language}/{controller}/{action}')
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
use AntonioKadid\WAPPKitCore\HTTP\Routing\Router;

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

Router::get('{language}/{controller}/{action}')
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
use AntonioKadid\WAPPKitCore\HTTP\Routing\Router;

// This route will be executed if language has value 'en' or 'el' and if controller has value 'hello'.
Router::get('{language}/{controller}/{action}')
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

Exception handling.

```php
use AntonioKadid\WAPPKitCore\HTTP\Routing\Router;

// This route will be executed if language has value 'en' or 'el' and if controller has value 'hello'.
Router::get('{language}/{controller}/{action}')
    ->then(function($language, $controller, $action) {
    
        throw new \Exception('Custom exception');
    
        return nl2br(sprintf("%s\n%s\n%s", $language, $controller, $action));
    })
    ->catch(function(Exception $exception){
        return $exception->getMessage();
        
        // You can also skip return statement and do something else like
        // die($exception->getMessage()) or echo 'Something went wrong.';
    });

$returnedRouteValue = Router::execute();

echo $returnedRouteValue;

/**
 * URL: en/hello/test
 *
 * Output:
 * Custom exception
 */
```
## LICENSE

MIT license.
