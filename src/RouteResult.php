<?php

namespace Router;

/**
 * Class RouteResult
 *
 * @package Router
 */
abstract class RouteResult implements IRouteResult
{
    /**
     * Generate the result.
     *
     * @return string
     */
    public abstract function produce(): string;

    /**
     * Output the result.
     */
    public final function output(): void
    {
        if (ob_get_length() !== FALSE)
            ob_clean();

        echo $this->produce();
    }

    /**
     * Set response content type.
     *
     * @param string $contentType
     */
    public final function setContentType(string $contentType): void
    {
        header_remove("CONTENT-TYPE");
        header("CONTENT-TYPE: {$contentType}");
    }

    /**
     * Set response HTTP status code.
     *
     * @param int $responseCode
     */
    public final function setStatusCode(int $responseCode): void
    {
        http_response_code($responseCode);
    }
}