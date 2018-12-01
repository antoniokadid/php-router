<?php

namespace Router;

use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;

/**
 * Class Router
 *
 * @package Router
 */
class Router
{
    const ERR_UNABLE_TO_DETECT_PARAM_VALUE = -7;
    const ERR_UNABLE_TO_DETECT_PARAM_TYPE = -6;
    const ERR_UNABLE_TO_HANDLE = -5;
    const ERR_UNDEF_INJECTION_HANDLER = -4;
    const ERR_UNDEF_ACCESS_HANDLER = -3;
    const ERR_UNDEF_AUTH_HANDLER = -2;
    const ERR_REG_EMPTY = -1;
    /** @var int Unauthorized */
    const ERR_HTTP_401 = 401;
    /** @var int Forbidden */
    const ERR_HTTP_403 = 403;
    /** @var int Not found */
    const ERR_HTTP_404 = 404;
    /** @var int Method not allowed */
    const ERR_HTTP_405 = 405;
    /** @var int Internal server error */
    const ERR_HTTP_500 = 500;

    /** @var string  */
    public static $routePrefix = '';

    /** @var callable */
    private static $authHandler = NULL;
    /** @var callable */
    private static $accessHandler = NULL;
    /** @var callable */
    private static $injectionHandler = NULL;
    /** @var array */
    private static $routes = [];

    /**
     * Register a route.
     *
     * @param string $method
     * @param string $route
     * @param $implementation
     * @param bool $authRequired
     * @param bool $accessRequired
     */
    public static function register(string $method, string $route, $implementation, bool $authRequired = FALSE, bool $accessRequired = FALSE): void
    {
        self::$routes[$method][self::processAsPattern($route)] = [$route, $implementation, $authRequired, $accessRequired];
    }

    /**
     * Register a callable that will validate if user is authenticated.
     *
     * @param callable|null $handler
     */
    public static function registerAuthenticationHandler(?callable $handler): void
    {
        self::$authHandler = $handler;
    }

    /**
     * Register a callable that will validate if user has access to route.
     * @param callable|null $handler
     */
    public static function registerAccessHandler(?callable $handler): void
    {
        self::$accessHandler = $handler;
    }

    /**
     * Register a callable that will instantiate and return an object based on the parameters passed from URL.
     *
     * @param callable|null $handler
     */
    public static function registerInjectionHandler(?callable $handler): void
    {
        self::$injectionHandler = $handler;
    }

    /**
     * @return RouteResult
     *
     * @throws RouterException
     */
    public static function handle(): ?IRouteResult
    {
        if (empty(self::$routes))
            throw new RouterException('Route registry is empty.', self::ERR_REG_EMPTY);

        $method = $_SERVER['REQUEST_METHOD'];

        if (!array_key_exists($method, self::$routes))
            throw new RouterException('Method not allowed', self::ERR_HTTP_405);

        $url = strtok($_SERVER["REQUEST_URI"], '?');
        $queryParams = [];
        parse_str($_SERVER['QUERY_STRING'], $queryParams);

        if (is_string(self::$routePrefix) && !empty(self::$routePrefix))
            $url = strpos($url, self::$routePrefix) === 0 ? substr($url, strlen(self::$routePrefix)) : $url;

        foreach (self::$routes[$method] as $pattern => $routeParameters) {
            if (!preg_match("/^{$pattern}$/i", $url, $urlParameters))
                continue;

            list($route, $impl, $authRequired, $accessRequired) = $routeParameters;

            if ($authRequired) {
                if (self::$authHandler == NULL || !is_callable(self::$authHandler))
                    throw new RouterException('Authentication handler is undefined.', self::ERR_UNDEF_AUTH_HANDLER);

                if (call_user_func_array(self::$authHandler, [$route]) !== TRUE)
                    throw new RouterException('Unauthorized', self::ERR_HTTP_401);
            }

            if ($accessRequired) {
                if (self::$accessHandler == NULL || !is_callable(self::$accessHandler))
                    throw new RouterException('Access handler is undefined.', self::ERR_UNDEF_ACCESS_HANDLER);

                if (call_user_func_array(self::$accessHandler, [$route]) !== TRUE)
                    throw new RouterException('Forbidden', self::ERR_HTTP_403);
            }

            if (is_string($impl))
                return self::handleString($impl, $urlParameters + $queryParams);
            else if (is_callable($impl))
                return self::handleCallable($impl, $urlParameters + $queryParams);
        }

        throw new RouterException('Not found', self::ERR_HTTP_404);
    }


    /**
     * @param string $name
     * @param array $parameters
     *
     * @return null|RouteResult
     *
     * @throws RouterException
     */
    private static function handleString(string $name, array $parameters): ?IRouteResult
    {
        if (class_exists($name)) {
            try {
                $refClass = new ReflectionClass($name);

                if (!$refClass->implementsInterface('Sculptor\\Routing\\IRouteImplementation'))
                    throw new RouterException(sprintf('Unable to handle route with %s.', $name), self::ERR_UNABLE_TO_HANDLE, ['name' => $name]);

                $invokeParameters = self::getInvokeParameters(
                    $refClass->getConstructor()->getParameters(),
                    $parameters);

                $instance = $refClass->newInstanceArgs($invokeParameters);

                return $refClass->getMethod('handle')->invoke($instance, []);
            } catch (ReflectionException $ex) {
                throw new RouterException(sprintf('Unable to handle route with %s.', $name), self::ERR_UNABLE_TO_HANDLE, ['name' => $name], $ex);
            }
        }

        if (is_callable($name))
            return self::handleCallable($name, $parameters);

        throw new RouterException(sprintf('Unable to handle route with %s.', $name), self::ERR_UNABLE_TO_HANDLE . ['name' => $name]);
    }

    /**
     * @param callable $implementation
     * @param array $parameters
     *
     * @return null|RouteResult
     *
     * @throws RouterException
     */
    private static function handleCallable(callable $implementation, array $parameters): ?IRouteResult
    {
        try {
            $refFunction = new ReflectionFunction($implementation);

            $invokeParameters = self::getInvokeParameters(
                $refFunction->getParameters(),
                $parameters);

            $result = $refFunction->invokeArgs($invokeParameters);

            if ($result !== FALSE && ($result == NULL || ($result instanceof IRouteResult)))
                return $result;

            throw new RouterException('Unable to handle route with callable.', self::ERR_UNABLE_TO_HANDLE);

        } catch (ReflectionException $ex) {
            throw new RouterException('Unable to handle route with callable.', self::ERR_UNABLE_TO_HANDLE, [], $ex);
        }
    }

    /**
     * @param ReflectionParameter[] $expectedInvokeParameters
     * @param array $parameters
     *
     * @return array
     *
     * @throws RouterException
     */
    private static function getInvokeParameters(array $expectedInvokeParameters, array $parameters): array
    {
        $result = [];

        foreach ($expectedInvokeParameters as $parameter) {
            $parameterType = $parameter->getType();
            $parameterName = $parameter->getName();

            if ($parameterType == NULL && array_key_exists($parameterName, $parameters)) {
                $result[] = $parameters[$parameterName];
                continue;
            }

            $parameterTypeName = $parameterType->getName();

            if ($parameterType->isBuiltin()) {

                if (array_key_exists($parameterName, $parameters)) {
                    switch ($parameterTypeName) {
                        case 'string':
                            $result[] = strval($parameters[$parameterName]);
                            break;
                        case 'bool':
                            $result[] = boolval($parameters[$parameterName]);
                            break;
                        case 'int':
                        case 'long':
                            $result[] = intval($parameters[$parameterName]);
                            break;
                        case 'float':
                        case 'double':
                            $result[] = floatval($parameters[$parameterName]);
                            break;
                        default:
                            if ($parameterType->allowsNull())
                                $result[] = NULL;
                            else
                                throw new RouterException(
                                    sprintf('Unable to detect parameter type for %s.', $parameterTypeName),
                                    self::ERR_UNABLE_TO_DETECT_PARAM_TYPE,
                                    ['name' => $parameterTypeName]);
                    }
                } else {
                    if ($parameterType->allowsNull())
                        $result[] = NULL;
                    else
                        throw new RouterException(
                            sprintf('Unable to detect parameter value for %s.', $parameterTypeName),
                            self::ERR_UNABLE_TO_DETECT_PARAM_VALUE,
                            ['name' => $parameterTypeName]);
                }

                continue;
            }

            if (self::$injectionHandler == NULL || !is_callable(self::$injectionHandler))
                throw new RouterException(
                    sprintf('Injection handler is undefined and cannot inject $s.', $parameterTypeName),
                    self::ERR_UNDEF_INJECTION_HANDLER,
                    ['name' => $parameterTypeName]);

            $injectionResult = call_user_func_array(self::$injectionHandler, [$parameterTypeName, $parameters]);

            if ($injectionResult == NULL && $parameterType->allowsNull())
                $result[] = NULL;
            else if (is_object($injectionResult) && (get_class($injectionResult) === $parameterTypeName))
                $result[] = $injectionResult;
            else
                throw new RouterException(
                    sprintf('Unable to detect parameter value for %s.', $parameterTypeName),
                    self::ERR_UNABLE_TO_DETECT_PARAM_VALUE,
                    ['name' => $parameterTypeName]);
        }

        return $result;
    }

    /**
     * Converts a route into regex pattern.
     *
     * @param string $route
     *
     * @return string
     */
    private static function processAsPattern(string $route): string
    {
        // temporarily replace :var to @@var@@
        // we do this because we want to escape slashes in patten which are part of url
        // and then we put back the :var with some regular expression goodies.
        $preRegex = preg_replace('/\\:(\\w+)/i', '@@\\1@@', $route);

        // generate a regex that will have named groups based on :var
        $regex = preg_replace_callback('/@@(\w+)@@/i', function ($match) {
            return "(?<{$match[1]}>(?:[^\\/]+))";
        }, preg_quote($preRegex, '/'));

        return $regex;
    }
}