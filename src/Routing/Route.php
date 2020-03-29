<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Routing;

use Closure;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;
use Throwable;

/**
 * Class Route
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Routing
 */
class Route
{
    /** @var array */
    private $_conditions = NULL;
    /** @var callable|NULL */
    private $_throwableHandler = NULL;
    /** @var callable|NULL */
    private $_implementationHandler = NULL;
    /** @var array */
    private $_urlQueryParams = [];

    /**
     * Route constructor.
     *
     * @param array $urlQueryParams
     */
    public function __construct(array $urlQueryParams = [])
    {
        $this->_urlQueryParams = $urlQueryParams;
    }

    /**
     * @param array $parameters
     * @param array $reflectionParameters
     *
     * @return array
     *
     * @throws ReflectionException
     */
    private static function getInvokeArgs(array $parameters, array $reflectionParameters): array
    {
        $args = [];

        /** @var ReflectionParameter $reflectionParameter */
        foreach ($reflectionParameters as $reflectionParameter) {
            $parameterName = $reflectionParameter->getName();

            if (!$reflectionParameter->hasType()) {

                if (array_key_exists($parameterName, $parameters))
                    $args[] = urldecode($parameters[$parameterName]);
                else if ($reflectionParameter->isOptional())
                    $args[] = $reflectionParameter->getDefaultValue();
                else
                    $args[] = NULL;

                continue;
            }

            $parameterType = $reflectionParameter->getType();
            $parameterTypeName = $parameterType->getName();

            if (!array_key_exists($parameterName, $parameters)) {
                if ($reflectionParameter->isOptional())
                    $args[] = $reflectionParameter->getDefaultValue();
                else if ($parameterType->allowsNull())
                    $args[] = NULL;
                else
                    throw new InvalidArgumentException(sprintf('Invalid value for parameter %s', $parameterName));

                continue;
            }

            $parameterValue = urldecode($parameters[$parameterName]);

            if (strcasecmp($parameterTypeName, 'string') === 0)
                $args[] = strval($parameterValue);
            else if (strcasecmp($parameterTypeName, 'bool') === 0)
                $args[] = boolval($parameterValue);
            else if (strcasecmp($parameterTypeName, 'int') === 0)
                $args[] = intval($parameterValue);
            else if (strcasecmp($parameterTypeName, 'float') === 0)
                $args[] = floatval($parameterValue);
            else if (strcasecmp($parameterTypeName, 'array') === 0 && is_array($parameterValue))
                $args[] = $parameterValue;
            else {
                $injectable = $reflectionParameter->getClass();

                if ($injectable == NULL || !$injectable->isInstantiable()) {
                    if ($reflectionParameter->isOptional())
                        $args[] = $reflectionParameter->getDefaultValue();
                    else if ($parameterType->allowsNull())
                        $args[] = NULL;
                    else
                        throw new InvalidArgumentException(sprintf('Invalid value for parameter %s', $parameterName));

                    continue;
                }

                $injectableConstructor = $injectable->getConstructor();
                if ($injectableConstructor == NULL || $injectableConstructor->getNumberOfParameters() === 0)
                    $args[] = $injectable->newInstanceArgs([]);
                else
                    $args[] = $injectable->newInstanceArgs(
                        self::getInvokeArgs($parameters, $injectableConstructor->getParameters()));
            }
        }

        return $args;
    }

    /**
     * Throwable handler for route.
     *
     * @param callable $callable
     *
     * @return Route
     */
    public function catch(callable $callable): Route
    {
        $this->_throwableHandler = $callable;

        return $this;
    }

    /**
     * Configure route.
     *
     * @param callable $callable
     *
     * @return Route
     */
    public function then(callable $callable): Route
    {
        $this->_implementationHandler = $callable;

        return $this;
    }

    /**
     * @param array $conditions
     *
     * @return Route
     */
    public function if(array $conditions): Route
    {
        $this->_conditions = $conditions;

        return $this;
    }

    /**
     * @return mixed|null
     *
     * @throws Throwable
     */
    public function execute()
    {
        try {
            if (!is_callable($this->_implementationHandler))
                die('Undefined route implementation detected.');

            if ($this->checkConditions() !== TRUE)
                return NULL;

            $function = new ReflectionFunction(Closure::fromCallable($this->_implementationHandler));

            return $function->invokeArgs(self::getInvokeArgs($this->_urlQueryParams, $function->getParameters()));
        } catch (Throwable $throwable) {
            if ($throwable instanceof ReflectionException)
                die($throwable->getMessage());

            if (!is_callable($this->_throwableHandler))
                throw $throwable;

            return call_user_func($this->_throwableHandler, $throwable);
        }
    }

    /**
     * @return bool
     */
    public function hasThrowableHandler(): bool
    {
        return is_callable($this->_throwableHandler);
    }

    /**
     * @return bool
     */
    private function checkConditions(): bool
    {
        if (!is_array($this->_conditions) || count($this->_urlQueryParams) === 0)
            return TRUE;

        foreach ($this->_conditions as $paramName => $condition) {
            if (is_callable($condition)) {
                if (is_int($paramName) && (call_user_func($condition) !== TRUE))
                    return FALSE;
                else if (array_key_exists($paramName, $this->_urlQueryParams) && (call_user_func($condition, urldecode($this->_urlQueryParams[$paramName])) !== TRUE))
                    return FALSE;

                continue;
            }

            if (!array_key_exists($paramName, $this->_urlQueryParams) || !is_string($condition))
                continue;

            $result = @preg_match($condition, urldecode($this->_urlQueryParams[$paramName]));
            if ($result === FALSE)
                die(sprintf('Invalid regular expresion for %s', $paramName));

            if ($result === 0)
                return FALSE;
        }

        return TRUE;
    }
}