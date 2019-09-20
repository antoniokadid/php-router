<?php

namespace AntonioKadid\Routing;

use Throwable;

/**
 * Class Router
 *
 * @package AntonioKadid\Routing
 */
class Router
{
    /** @var Route */
    private static $_activatedRoute = NULL;

    /**
     * Router constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function get(string $route): Route
    {
        return self::register('GET', $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function post(string $route): Route
    {
        return self::register('POST', $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function put(string $route): Route
    {
        return self::register('PUT', $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function patch(string $route): Route
    {
        return self::register('PATCH', $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function delete(string $route): Route
    {
        return self::register('DELETE', $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function options(string $route): Route
    {
        return self::register('OPTIONS', $route);
    }

    /**
     * @param string $method
     * @param string ...$route
     *
     * @return Route
     */
    public static function register(string $method, string ...$routes): Route
    {
        if (self::$_activatedRoute != NULL || strcasecmp($_SERVER['REQUEST_METHOD'], $method) !== 0)
            return new Route();

        foreach ($routes as $route) {
            $pattern = Router::routeToRegExPattern($route);
            if (@preg_match($pattern, $_SERVER['REQUEST_URI'], $urlParameters) === 1) {
                parse_str($_SERVER['QUERY_STRING'], $queryParameters);
                self::$_activatedRoute = new Route($urlParameters + $queryParameters);

                return self::$_activatedRoute;
            }
        }

        return new Route();
    }

    /**
     * @return mixed|null
     *
     * @throws Throwable
     */
    public static function execute()
    {
        if (self::$_activatedRoute != NULL)
            return self::$_activatedRoute->execute();

        return NULL;
    }

    /**
     * Converts a route into regex pattern.
     *
     * @param string $route
     *
     * @return string
     */
    private static function routeToRegExPattern(string $route): string
    {
        // temporarily replace :var to @@var@@
        // we do this because we want to escape slashes in patten which are part of url
        // and then we put back the :var with some regular expression goodies.
        $preRegex = preg_replace('/\\:(\\w+)/i', '@@\\1@@', $route);

        // generate a regex that will have named groups based on :var
        $regex = preg_replace_callback('/@@(\w+)@@/i', function ($match) {
            return "(?<{$match[1]}>(?:[^\\/\\?]+))";
        }, preg_quote($preRegex, '/'));

        if (strpos($regex, '\/') !== 0)
            $regex = "\\/{$regex}";

        return "/^{$regex}(?:\\?.*$|$)/i";
    }
}