<?php

namespace AntonioKadid\WAPPKitCore\HTTP;

/**
 * Class URL
 *
 * @package AntonioKadid\WAPPKitCore\HTTP
 */
class URL
{
    /**
     * URL constructor.
     *
     * @param string $url
     */
    public function __construct(string $url = '')
    {
        if (!empty($url))
            $this->processUrl($url);
    }

    /** @var string */
    public $scheme;

    /** @var string */
    public $username;

    /** @var string */
    public $password;

    /** @var string */
    public $host;

    /** @var int */
    public $port;

    /** @var string */
    public $path;

    /** @var array */
    public $query = [];

    /** @var string */
    public $fragment;

    /**
     * @return string
     */
    public function __toString()
    {
        $url = '';

        if (!empty($this->scheme))
            $url .= sprintf('%s://', $this->scheme);

        if (!empty($this->username) && !empty($this->password))
            $url .= sprintf('%s:%s@', $this->username, $this->password);

        if (!empty($this->host))
        {
            if (is_int($this->port) && $this->port > 0)
                $url .= sprintf('%s:%d', $this->host, $this->port);
            else
                $url .= $this->host;
        }

        if (!empty($this->path))
            $url .= $this->path;

        if (is_array($this->query))
            $url .= sprintf('?%s', http_build_query($this->query));

        if (!empty($this->fragment))
            $url .= sprintf('#%s', $this->fragment);

        return $url;
    }

    /**
     * @param string $url
     */
    private function processUrl(string $url): void
    {
        $urlData = parse_url($url);
        if ($urlData === FALSE)
            return;

        if (array_key_exists('scheme', $urlData))
            $this->scheme = $urlData['scheme'];

        if (array_key_exists('user', $urlData))
            $this->username = $urlData['user'];

        if (array_key_exists('pass', $urlData))
            $this->password = $urlData['pass'];

        if (array_key_exists('host', $urlData))
            $this->host = $urlData['host'];

        if (array_key_exists('port', $urlData))
            $this->port = $urlData['port'];

        if (array_key_exists('path', $urlData))
            $this->path = $urlData['path'];

        if (array_key_exists('query', $urlData)) {
            parse_str($urlData['query'], $query);
            $this->query = !is_array($query) ? [] : $query;
        }

        if (array_key_exists('fragment', $urlData))
            $this->fragment = $urlData['fragment'];
    }
}