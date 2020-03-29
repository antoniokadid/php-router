<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Request;

use AntonioKadid\WAPPKitCore\Collections\Map;

/**
 * Class Request
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Request
 */
abstract class Request extends Map
{
    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string
     */
    public function getQueryString(): string
    {
        return $_SERVER['QUERY_STRING'];
    }

    /**
     * @return string|null
     */
    public function getRemoteIp(): ?string
    {
        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']))
            return $_SERVER['HTTP_CLIENT_IP'];

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            return $_SERVER['HTTP_X_FORWARDED_FOR'];

        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'] != ''))
            return $_SERVER['REMOTE_ADDR'];

        return NULL;
    }
}