<?php


namespace AntonioKadid\WAPPKitCore\HTTP\Client;

use AntonioKadid\WAPPKitCore\HTTP\Headers;

/**
 * Class CURLOptions
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Client
 */
class CURLOptions
{
    /**
     * CURLOptions constructor.
     */
    public function __construct()
    {
    }

    /** @var bool */
    public $verifyHost = FALSE;
    /** @var bool */
    public $verifyPeer = FALSE;
    /** @var bool */
    public $verifyCertificateStatus = FALSE;
    /** @var int */
    public $connectTimeout = 0;
    /** @var int */
    public $executionTimeout = 0;
    /** @var Headers */
    public $headers = NULL;

    /**
     * @param resource $curl CURL Resource
     */
    public function setup($curl): void
    {
        if ($this->headers != NULL)
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers->asCURLHeaders());

        // SSL
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->verifyHost === TRUE ? 2 : 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->verifyPeer === TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYSTATUS, $this->verifyCertificateStatus === TRUE);

        // TIMEOUTS
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->executionTimeout);
    }
}