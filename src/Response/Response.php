<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Response;

use AntonioKadid\WAPPKitCore\Http\Status;

/**
 * Class Response
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Response
 */
abstract class Response implements IResponse
{
    /** @var int */
    protected $httpStatus = Status::OK;

    /**
     * @param int $httpStatusCode
     *
     * @return static
     */
    public function setHttpStatusCode(int $httpStatusCode)
    {
        $this->httpStatus = $httpStatusCode;

        return $this;
    }

    public abstract function output();
}