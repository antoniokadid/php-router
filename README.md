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

GET request *en/hello/test*

URL keywords are defined with colon (:).

```php
use Router\Router;

Router::register('GET', ':language/:controller/:action', function ($language, $controller, $action) {
    echo nl2br(sprintf("%s\n%s\n%s", $language, $controller, $action));
});

Router::handle();

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
use Router\Router;

Router::register('GET', ':language/:controller/:action', function ($action, $controller, $language) {
    echo nl2br(sprintf("%s\n%s\n%s", $language, $controller, $action));
});

Router::handle();

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
use Router\Router;
use Router\IRouteImplementation;
use Router\IRouteResult;

class HelloController implements IRouteImplementation
{
    private $language;
    private $action;

    function __construct($language, $controller, $action)
    {
        $this->language = $language;
        $this->action = $action;
    }

    function handle(): ?IRouteResult
    {
        echo nl2br(sprintf("%s\n%s\n%s", $this->language, 'hello', $this->action));

        return NULL;
    }
}

Router::register('GET', ':language/:controller/:action', 'HelloController');

Router::handle();

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
use Router\Router;
use Router\IRouteImplementation;
use Router\IRouteResult;

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

class HelloController implements IRouteImplementation
{
    private $language;
    private $action;

    // Intentionally specify parameter type for language. This will invoke the injection handler.
    function __construct(Language $language, $action)
    {
        $this->language = $language;
        $this->action = $action;
    }

    function handle(): ?IRouteResult
    {
        echo nl2br(sprintf("%s\n%s\n%s", $this->language->getCode(), 'hello', $this->action));

        return NULL;
    }
}

Router::registerInjectionHandler(function($parameterType, $parameterName, $urlParameters) {
    if ($parameterType == "Language")
        return new Language($urlParameters[$parameterName]);

    return NULL;
});

Router::register('GET', ':language/:controller/:action', 'HelloController');

Router::handle();

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