<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Request;

use AntonioKadid\WAPPKitCore\Text\Exceptions\DecodingException;
use AntonioKadid\WAPPKitCore\Text\JSON\JSONDecoder;

/**
 * Class JSONRequest
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Request
 */
class JSONRequest extends Request
{
    /**
     * JSONRequest constructor.
     *
     * @throws DecodingException
     */
    public function __construct()
    {
        $decoder = new JSONDecoder(TRUE);
        $data = $decoder->decode(file_get_contents('php://input'));

        parent::__construct(($data === NULL || !is_array($data)) ? [] : $data);
    }
}