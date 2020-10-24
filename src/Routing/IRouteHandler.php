<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Routing;

/**
 * Interface IRouteHandler
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Routing
 */
interface IRouteHandler
{
    /**
     * @param string $method
     *
     * @return bool
     */
    function isMethodAllowed(string $method): bool;

    /**
     * @return callable
     */
    function getImplementationHandler(): ?callable;

    /**
     * @return callable|null
     */
    function getErrorHandler(): ?callable;
}