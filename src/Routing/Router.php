<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Routing;

use AntonioKadid\WAPPKitCore\HTTP\Method;
use Throwable;

/**
 * Class Router
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Routing
 */
final class Router
{
    private static $_routerInstance = NULL;
    /** @var Route */
    private static $_activatedRoute = NULL;
    /** @var callable|NULL */
    private $_globalThrowableHandler = NULL;

    /**
     * Router constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return Router
     */
    public static function init(): Router
    {
        if (self::$_routerInstance == NULL)
            self::$_routerInstance = new Router();

        return self::$_routerInstance;
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function get(string $route): Route
    {
        return self::register(Method::GET, $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function post(string $route): Route
    {
        return self::register(Method::POST, $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function put(string $route): Route
    {
        return self::register(Method::PUT, $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function patch(string $route): Route
    {
        return self::register(Method::PATCH, $route);
    }

    /**
     * @param string $route
     *
     * @return Route
     */
    public static function delete(string $route): Route
    {
        return self::register(Method::DELETE, $route);
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

    /**
     * @param callable $callable
     *
     * @return Router
     */
    public function catch(callable $callable): Router
    {
        $this->_globalThrowableHandler = $callable;

        return $this;
    }

    /**
     * @return mixed|null
     *
     * @throws Throwable
     */
    public function execute()
    {
        if (self::$_activatedRoute == NULL)
            return NULL;

        if (self::$_activatedRoute->hasThrowableHandler())
            return self::$_activatedRoute->execute();

        try {
            return self::$_activatedRoute->execute();
        } catch (Throwable $throwable) {
            if (is_callable($this->_globalThrowableHandler))
                return call_user_func($this->_globalThrowableHandler, $throwable);

            throw $throwable;
        }
    }
}