<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Request;

/**
 * Class PostRequest
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Request
 */
class PostRequest extends Request
{
    /**
     * PostRequest constructor.
     */
    public function __construct()
    {
        parent::__construct($_POST);
    }
}