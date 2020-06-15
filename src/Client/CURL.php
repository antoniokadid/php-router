<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Client;

use AntonioKadid\WAPPKitCore\HTTP\Exceptions\CURLException;
use AntonioKadid\WAPPKitCore\HTTP\Headers;
use AntonioKadid\WAPPKitCore\HTTP\Method;
use AntonioKadid\WAPPKitCore\HTTP\URL;
use AntonioKadid\WAPPKitCore\Text\Exceptions\EncodingException;
use AntonioKadid\WAPPKitCore\Text\JSON\JSONEncoder;

/**
 * Class CURL
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Client
 */
class CURL
{
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    /** @var false|resource */
    private $_curlRef;

    /**
     * CURL constructor.
     */
    public function __construct()
    {
        $this->_curlRef = curl_init();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close(): void
    {
        if (is_resource($this->_curlRef))
            curl_close($this->_curlRef);
    }

    /**
     * Perform a GET request.
     *
     * @param URL              $url
     * @param array            $data
     * @param CURLOptions|null $options
     *
     * @return CURLResult
     *
     * @throws CURLException
     */
    public function get(URL $url, array $data = [], ?CURLOptions $options = NULL): CURLResult
    {
        return $this->getLikeRequest(Method::GET, $url, $data, $options);
    }

    /**
     * @param URL              $url
     * @param array            $data
     * @param CURLOptions|null $options
     *
     * @return CURLResult
     *
     * @throws CURLException
     */
    public function delete(URL $url, array $data = [], ?CURLOptions $options = NULL): CURLResult
    {
        return $this->getLikeRequest(Method::DELETE, $url, $data, $options);
    }

    /**
     * @param URL              $url
     * @param array            $data
     * @param CURLOptions|null $options
     *
     * @return CURLResult
     *
     * @throws CURLException
     * @throws EncodingException
     */
    public function post(URL $url, array $data = [], ?CURLOptions $options = NULL): CURLResult
    {
        return $this->postLikeRequest(Method::POST, $url, $data, $options);
    }

    /**
     * @param URL              $url
     * @param array            $data
     * @param CURLOptions|null $options
     *
     * @return CURLResult
     *
     * @throws CURLException
     * @throws EncodingException
     */
    public function patch(URL $url, array $data = [], ?CURLOptions $options = NULL): CURLResult
    {
        return $this->postLikeRequest(Method::PATCH, $url, $data, $options);
    }

    /**
     * @param URL              $url
     * @param array            $data
     * @param CURLOptions|null $options
     *
     * @return CURLResult
     *
     * @throws CURLException
     * @throws EncodingException
     */
    public function put(URL $url, array $data = [], ?CURLOptions $options = NULL): CURLResult
    {
        return $this->postLikeRequest(Method::PUT, $url, $data, $options);
    }

    /**
     * @param string           $method
     * @param URL              $url
     * @param array            $data
     * @param CURLOptions|null $options
     *
     * @return CURLResult
     *
     * @throws CURLException
     * @throws EncodingException
     */
    private function postLikeRequest(string $method, URL $url, array $data = [], ?CURLOptions $options = NULL): CURLResult
    {
        if ($options == NULL)
            $options = new CURLOptions();

        if ($options->headers == NULL)
            $options->headers = new Headers();

        if ($options->headers->getTrimString(Headers::ContentType) === self::CONTENT_TYPE_JSON) {
            $jsonEncoder = new JSONEncoder();
            $postData = $jsonEncoder->encode($data);

            curl_setopt($this->_curlRef, CURLOPT_POSTFIELDS, $postData);

            $options->headers[Headers::ContentLength] = strlen($postData);
        } else {
            $query = http_build_query($data);

            curl_setopt($this->_curlRef, CURLOPT_POSTFIELDS, $query);

            $options->headers[Headers::ContentType] = self::CONTENT_TYPE_FORM_URLENCODED;
            $options->headers[Headers::ContentLength] = strlen($query);
        }

        curl_setopt($this->_curlRef, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->_curlRef, CURLOPT_URL, $url->__toString());

        return $this->execute($options);
    }

    /**
     * @param string           $method
     * @param URL              $url
     * @param array            $data
     * @param CURLOptions|null $options
     *
     * @return CURLResult
     * @throws CURLException
     */
    private function getLikeRequest(string $method, URL $url, array $data = [], ?CURLOptions $options = NULL)
    {
        $url->query = !is_array($url->query) ? $data : array_merge($url->query, $data);

        curl_setopt($this->_curlRef, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->_curlRef, CURLOPT_URL, $url->__toString());

        return $this->execute($options);
    }

    /**
     * @param CURLOptions|null $options
     *
     * @return CURLResult
     *
     * @throws CURLException
     */
    private function execute(?CURLOptions $options = NULL): CURLResult
    {
        if ($options != NULL)
            $options->setup($this->_curlRef);

        curl_setopt($this->_curlRef, CURLOPT_HEADER, 1);
        curl_setopt($this->_curlRef, CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($this->_curlRef);

        if (curl_errno($this->_curlRef) !== 0)
            throw new CURLException(curl_error($this->_curlRef), curl_errno($this->_curlRef));

        $code = curl_getinfo($this->_curlRef, CURLINFO_RESPONSE_CODE);
        $headersSize = curl_getinfo($this->_curlRef, CURLINFO_HEADER_SIZE);

        $responseHeaders = Headers::fromString(substr($response, 0, $headersSize));
        $responseBody = substr($response, $headersSize);

        return new CURLResult($code, $responseHeaders, $responseBody);
    }
}