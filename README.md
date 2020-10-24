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
use AntonioKadid\WAPPKitCore\HTTP\Routing\IRouteHandler;
use AntonioKadid\WAPPKitCore\HTTP\Method;

class RouteHandler implements IRouteHandler
{
    function isMethodAllowed(string $method): bool
    {
        return $method === Method::GET;
    }

    function getImplementationHandler(): ?callable
    {
        return [$this, 'countRouteHandler'];
    }

    function getErrorHandler(): ?callable
    {
        return [$this, 'error'];
    }

    function countRouteHandler(int $count)
    {
        return $count;
    }

    function error(Throwable $throwable)
    {
        return $throwable->getMessage();
    }
}

$router = Router::for(Method::GET, '/route/5');
$router->bind('/route/{count}', RouteHandler::class);
$result = $router->execute();

echo $result;

/**
 * URL: /route/5
 *
 * Output:
 *   5
 */
```

## LICENSE

MIT license.
