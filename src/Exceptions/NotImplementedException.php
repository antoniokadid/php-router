<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Exceptions;

use AntonioKadid\WAPPKitCore\HTTP\Status;
use Exception;
use Throwable;

/**
 * Class NotImplementedException
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Exceptions
 */
class NotImplementedException extends Exception
{
    /** @var string */
    private $_route;

    /**
     * NotImplementedException constructor.
     *
     * @param string         $route
     * @param Throwable|null $previous
     */
    public function __construct(string $route, Throwable $previous = NULL)
    {
        parent::__construct('Not implemented.', Status::NotImplemented, $previous);

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