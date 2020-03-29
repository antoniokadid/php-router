<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Request;

/**
 * Class GetRequest
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Request
 */
class GetRequest extends Request
{
    /**
     * GetRequest constructor.
     */
    public function __construct()
    {
        parent::__construct($_GET);
    }
}