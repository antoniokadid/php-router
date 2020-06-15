<?php

namespace AntonioKadid\WAPPKitCore\Tests\HTTP;

use AntonioKadid\WAPPKitCore\HTTP\Client\CURL;
use AntonioKadid\WAPPKitCore\HTTP\Status;
use AntonioKadid\WAPPKitCore\HTTP\URL;
use PHPUnit\Framework\TestCase;

/**
 * Class CURLGetTest
 *
 * Powered by https://postman-echo.com
 *
 * @package AntonioKadid\WAPPKitCore\Tests\HTTP
 *
 */
class CURLGetTest extends TestCase
{
    public function testGet()
    {
        $curl = new CURL();

        $url = new URL('https://postman-echo.com/get');

        $result = $curl->get($url);

        $this->assertEquals(Status::OK, $result->getResponseCode());
        $data = json_decode($result->getBody(), TRUE);

        $this->assertArrayHasKey('args', $data);
        $this->assertEmpty($data['args']);

        $curl->close();
    }

    public function testGetWithParameters()
    {
        $curl = new CURL();

        $url = new URL('https://postman-echo.com/get');

        $result = $curl->get($url, ['foo1' => 'bar1']);

        $this->assertEquals(Status::OK, $result->getResponseCode());
        $data = json_decode($result->getBody(), TRUE);

        $this->assertArrayHasKey('args', $data);
        $this->assertIsArray($data['args']);
        $this->assertArrayHasKey('foo1', $data['args']);
        $this->assertEquals('bar1', $data['args']['foo1']);

        $curl->close();
    }

    public function testGetWithQueryParameters()
    {
        $curl = new CURL();

        $url = new URL('https://postman-echo.com/get?foo1=bar1&foo2=bar2');

        $result = $curl->get($url, []);

        $this->assertEquals(Status::OK, $result->getResponseCode());

        $data = json_decode($result->getBody(), TRUE);

        $this->assertArrayHasKey('args', $data);
        $this->assertIsArray($data['args']);
        $this->assertArrayHasKey('foo1', $data['args']);
        $this->assertArrayHasKey('foo2', $data['args']);
        $this->assertEquals('bar1', $data['args']['foo1']);
        $this->assertEquals('bar2', $data['args']['foo2']);

        $curl->close();
    }

    public function testGetWithQueryParametersAndArrayParameters()
    {
        $curl = new CURL();

        $url = new URL('https://postman-echo.com/get?foo1=bar1&foo2=bar2');

        $result = $curl->get($url, ['foo3' => 'bar3']);

        $data = json_decode($result->getBody(), TRUE);

        $this->assertArrayHasKey('args', $data);
        $this->assertIsArray($data['args']);
        $this->assertArrayHasKey('foo1', $data['args']);
        $this->assertArrayHasKey('foo2', $data['args']);
        $this->assertArrayHasKey('foo3', $data['args']);
        $this->assertEquals('bar1', $data['args']['foo1']);
        $this->assertEquals('bar2', $data['args']['foo2']);
        $this->assertEquals('bar3', $data['args']['foo3']);

        $curl->close();
    }

    public function testGetWithQueryParametersAndReplaceFromArrayParameters()
    {
        $curl = new CURL();

        $url = new URL('https://postman-echo.com/get?foo1=bar1&foo2=bar2');

        $result = $curl->get($url, ['foo1' => 'bar3']);

        $data = json_decode($result->getBody(), TRUE);

        $this->assertArrayHasKey('args', $data);
        $this->assertIsArray($data['args']);
        $this->assertArrayHasKey('foo1', $data['args']);
        $this->assertArrayHasKey('foo2', $data['args']);
        $this->assertEquals('bar3', $data['args']['foo1']);
        $this->assertEquals('bar2', $data['args']['foo2']);

        $curl->close();
    }
}