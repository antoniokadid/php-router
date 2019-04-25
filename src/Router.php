<?php

namespace AntonioKadid\Router;

/**
 * Class Router
 *
 * @package AntonioKadid\Router
 */
final class Router
{
    /** @var Route[] */
    private static $_registry = [];

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
     * @return array|mixed|NULL
     */
    public static function execute()
    {
        $result = array_map(function(Route $route) {
            return $route->execute();
        }, self::$_registry);

        $count = count($result);

        if ($count === 0)
            return NULL;
        else if ($count === 1)
            return array_shift($result);
        else
            return $result;
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

        return "/^.*{$regex}(?:\\?.*|$)/i";
    }

    /**
     * @param string $method
     * @param string $route
     *
     * @return Route
     */
    private static function register(string $method, string $route): Route
    {
        if (strcasecmp($_SERVER['REQUEST_METHOD'], $method) !== 0)
            return new Route();

        $pattern = Router::routeToRegExPattern($route);
        if (!@preg_match($pattern, $_SERVER['REQUEST_URI'], $urlParameters))
            return new Route();

        parse_str($_SERVER['QUERY_STRING'], $queryParameters);

        $route = new Route($urlParameters + $queryParameters);

        self::$_registry[] = $route;

        return $route;
    }
}