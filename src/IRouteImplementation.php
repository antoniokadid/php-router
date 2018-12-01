<?php

namespace Router;

/**
 * Interface IRouteImplementation
 *
 * @package Router
 */
interface IRouteImplementation
{
    function handle(): ?IRouteResult;
}