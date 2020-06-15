<?php

namespace AntonioKadid\WAPPKitCore\HTTP;

use AntonioKadid\WAPPKitCore\Collections\Map;

/**
 * Class Headers
 *
 * @package AntonioKadid\WAPPKitCore\HTTP
 *
 * @url https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
 */
class Headers extends Map
{
    /** @var string The size of the resource, in decimal number of bytes. */
    const ContentLength = 'Content-Length';

    /** @var string Content-Type */
    const ContentType = 'Content-Type';

    /**
     * @param string $headers
     *
     * @return Headers
     */
    public static function fromString(string $headers): Headers
    {
        $parts = preg_split('/\r\n|\r|\n/', trim($headers));

        $headers = new Headers();
        foreach ($parts as $part) {
            list($header, $value) = explode(':', $part);
            $headers[$header] = $value;
        }

        return $headers;
    }

    /**
     * @return array
     */
    public function asCURLHeaders(): array
    {
        if (empty($this->source))
            return $this->source;

        $result = [];

        foreach ($this->source as $key => $value)
            $result[] = sprintf('%s: %s', $key, $value);

        return $result;
    }
}