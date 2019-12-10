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

    /** @var callable|NULL */
    private static $_globalThrowableHandler = NULL;

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
        return self::register(HttpMethods::GET, $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function post(string $route): Route
    {
        return self::register(HttpMethods::POST, $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function put(string $route): Route
    {
        return self::register(HttpMethods::PUT, $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function patch(string $route): Route
    {
        return self::register(HttpMethods::PATCH, $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function delete(string $route): Route
    {
        return self::register(HttpMethods::DELETE, $route);
    }

    /**
     * @param string $method
     * @param mixed  ...$routes
     *
     * @return Route
     */
    public static function register(string $method, ...$routes): Route
    {
        if (self::$_activatedRoute != NULL || strcasecmp($_SERVER['REQUEST_METHOD'], $method) !== 0)
            return new Route();

        foreach ($routes as $route) {
            $pattern = Router::routeToRegExPattern(strval($route));
            if (@preg_match($pattern, $_SERVER['REQUEST_URI'], $urlParameters) !== 1)
                continue;

            parse_str($_SERVER['QUERY_STRING'], $queryParameters);
            self::$_activatedRoute = new Route($urlParameters + $queryParameters);

            return self::$_activatedRoute;
        }

        return new Route();
    }

    /**
     * @param callable $callable
     */
    public static function catch(callable $callable): void
    {
        self::$_globalThrowableHandler = $callable;
    }

    /**
     * @return mixed|null
     *
     * @throws Throwable
     */
    public static function execute()
    {
        if (self::$_activatedRoute == NULL)
            return NULL;

        if (self::$_activatedRoute->hasThrowableHandler())
            return self::$_activatedRoute->execute();

        try {
            return self::$_activatedRoute->execute();
        } catch (Throwable $throwable) {
            if (self::$_globalThrowableHandler != NULL && is_callable(self::$_globalThrowableHandler))
                return call_user_func(self::$_globalThrowableHandler, $throwable);

            throw $throwable;
        }
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
        $pattern = preg_replace_callback_array([
            '/\\{(\\w+)\\}/i' => function ($match) {
                return sprintf('(?<%s>(?:[^/\\?]+))', $match[1]);
            },
            '/\\*\\*/' => function ($match) {
                return '(?:[^\\?]+)';
            },
            '/\\*/' => function ($match) {
                return '(?:[^/\\?]+)';
            }
        ], $route);

        if (strpos($pattern, '/') !== 0)
            $pattern = "/{$pattern}";

        return sprintf('`^%s(?:\\?.*$|$)`i', $pattern);
    }
}