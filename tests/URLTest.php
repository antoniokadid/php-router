<?php

namespace AntonioKadid\WAPPKitCore\Tests\HTTP;

use AntonioKadid\WAPPKitCore\HTTP\URL;
use PHPUnit\Framework\TestCase;

class URLTest extends TestCase
{
    public function testToString()
    {
        $address = 'https://user:pass@www.test.com:8080/hello/world?param=param1#hashtag';

        $url = new URL($address);

        $this->assertEquals('https', $url->scheme);
        $this->assertEquals('user', $url->username);
        $this->assertEquals('pass', $url->password);
        $this->assertEquals('www.test.com', $url->host);
        $this->assertEquals(8080, $url->port);
        $this->assertEquals('/hello/world', $url->path);
        $this->assertEquals(['param' => 'param1'], $url->query);
        $this->assertEquals('hashtag', $url->fragment);

        $this->assertEquals($address, $url->__toString());
    }
}