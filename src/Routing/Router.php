<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Routing;

use AntonioKadid\WAPPKitCore\HTTP\Exceptions\MethodNotAllowedException;
use AntonioKadid\WAPPKitCore\HTTP\Exceptions\NotImplementedException;
use AntonioKadid\WAPPKitCore\Reflection\CallableInvoker;
use AntonioKadid\WAPPKitCore\Reflection\Exceptions\InvalidParameterValueException;
use AntonioKadid\WAPPKitCore\Reflection\Exceptions\UnknownParameterTypeException;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * Class Router
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Routing
 */
final class Router
{
    /** @var Router */
    private static $_routerInstance = NULL;
    /** @var callable|NULL */
    private $_globalThrowableHandler = NULL;
    /** @var string */
    private $_requestMethod;
    /** @var string */
    private $_requestUrl;
    /** @var string */
    private $_queryString;
    /** @var array */
    private $_routeRegistry = [];

    /**
     * Router constructor.
     *
     * @param string $requestMethod
     * @param string $requestUrl
     * @param string $queryString
     */
    private function __construct(string $requestMethod, string $requestUrl, string $queryString = '')
    {
        $this->_requestMethod = $requestMethod;
        $this->_requestUrl = $requestUrl;
        $this->_queryString = $queryString;
    }

    /**
     * @param string $requestMethod
     * @param string $requestUrl
     * @param string $queryString
     *
     * @return Router
     */
    public static function for(string $requestMethod, string $requestUrl, string $queryString = ''): Router
    {
        if (self::$_routerInstance == NULL)
            self::$_routerInstance = new Router($requestMethod, $requestUrl, $queryString);

        self::$_routerInstance->_requestMethod = $requestMethod;
        self::$_routerInstance->_requestUrl = $requestUrl;
        self::$_routerInstance->_queryString = $queryString;

        return self::$_routerInstance;
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
     * @param string $route
     * @param string $className
     */
    public function bind(string $route, string $className): void
    {
        $pattern = self::routeToRegExPattern($route);

        if (!array_key_exists($route, $this->_routeRegistry))
            $this->_routeRegistry[$route] = ['pattern' => $pattern, 'class' => $className];
    }

    /**
     * @param string $route
     */
    public function unbind(string $route): void
    {
        if (array_key_exists($route, $this->_routeRegistry))
            unset($this->_routeRegistry[$route]);

    }

    /**
     * @param array $routes
     */
    public function bindMany(array $routes): void
    {
        foreach ($routes as $route => $className)
            $this->bind(strval($route), strval($className));
    }

    /**
     * @return mixed|null
     *
     * @throws InvalidParameterValueException
     * @throws MethodNotAllowedException
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownParameterTypeException
     */
    public function execute()
    {
        try {
            if (empty($this->_routeRegistry))
                return NULL;

            $matchingRoute = $this->findMatchingRoute();
            if ($matchingRoute == NULL)
                return NULL;

            $route = $matchingRoute['route'];
            $class = $matchingRoute['class'];
            $data = $matchingRoute['data'];

            if (!class_exists($class, TRUE))
                return NULL;

            $class = new ReflectionClass($class);
            if (!$class->implementsInterface(IRouteHandler::class))
                return NULL;

            /** @var  $instance IRouteHandler */
            $instance = $class->newInstance();
            if (!$instance->isMethodAllowed($this->_requestMethod))
                throw new MethodNotAllowedException($route);

            $implementationHandler = $instance->getImplementationHandler();
            if ($implementationHandler == NULL)
                throw new NotImplementedException($route);

            $callableInvoker = new CallableInvoker($implementationHandler);

            try {
                return $callableInvoker->invoke($data);
            } catch (Throwable $throwable) {
                $errorHandler = $instance->getErrorHandler();
                if ($errorHandler != NULL)
                    return call_user_func($errorHandler, $throwable);

                throw $throwable;
            }
        } catch (Throwable $globalThrowable) {
            if (!is_callable($this->_globalThrowableHandler))
                throw $globalThrowable;

            return call_user_func($this->_globalThrowableHandler, $globalThrowable);
        }
    }

    /**
     * @param callable|null $callable
     *
     * @return $this
     */
    public function catch(?callable $callable): Router
    {
        $this->_globalThrowableHandler = $callable;

        return $this;
    }

    /**
     * @return array|null
     */
    private function findMatchingRoute(): ?array
    {
        $routePatterns = array_combine(array_keys($this->_routeRegistry), array_column($this->_routeRegistry, 'pattern'));
        if (empty($routePatterns))
            return NULL;

        foreach ($routePatterns as $route => $routePattern) {
            if (@preg_match($routePattern, $this->_requestUrl, $urlParameters) !== 1)
                continue;

            // URLDecode $urlParameters
            array_walk($urlParameters,
                function (&$urlEncodedParameter) {
                    $urlEncodedParameter = urldecode($urlEncodedParameter);
                });

            // Load query string
            parse_str($this->_queryString, $queryParameters);

            // Combine the two parameter arrays.
            $data = $urlParameters + $queryParameters;

            return [
                'route' => $route,
                'class' => $this->_routeRegistry[$route]['class'],
                'data' => $data
            ];
        }

        return NULL;
    }
}