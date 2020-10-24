<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Exceptions;

use AntonioKadid\WAPPKitCore\HTTP\Status;
use Exception;
use Throwable;

/**
 * Class MethodNotAllowedException
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Exceptions
 */
class MethodNotAllowedException extends Exception
{
    /** @var string */
    private $_route;

    /**
     * MethodNotAllowedException constructor.
     *
     * @param string         $route
     * @param Throwable|null $previous
     */
    public function __construct(string $route, Throwable $previous = NULL)
    {
        parent::__construct('Method not allowed.', Status::MethodNotAllowed, $previous);

        $this->_route = $route;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->_route;
    }
}