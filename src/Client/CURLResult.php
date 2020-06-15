<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Client;

use AntonioKadid\WAPPKitCore\HTTP\Headers;

/**
 * Class CURLResult
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Client
 */
class CURLResult
{
    /** @var Headers */
    private $_headers;
    /** @var string */
    private $_body;
    /** @var int */
    private $_responseCode;

    /**
     * CURLResult constructor.
     *
     * @param int    $responseCode
     * @param Headers  $headers
     * @param string $body
     */
    public function __construct(int $responseCode, Headers $headers, string $body)
    {
        $this->_responseCode = $responseCode;
        $this->_headers = $headers;
        $this->_body = $body;
    }

    /**
     * @return Headers
     */
    public function getHeaders(): Headers
    {
        return $this->_headers;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->_body;
    }

    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->_responseCode;
    }
}