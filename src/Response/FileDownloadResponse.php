<?php

namespace AntonioKadid\WAPPKitCore\HTTP\Response;

/**
 * Class FileDownloadResponse
 *
 * @package AntonioKadid\WAPPKitCore\HTTP\Response
 */
class FileDownloadResponse extends Response
{
    /** @var string */
    public $contentType;
    /** @var mixed */
    public $data;
    /** @var string */
    public $name;
    /** @var int */
    public $size;

    public function output(): void
    {
        if (!headers_sent()) {
            header_remove('Content-Type');
            header('Content-Type: application/json');
        }

        if (ob_get_length() !== FALSE)
            ob_clean();

        http_response_code($this->httpStatus);

        header("Content-Description: File Transfer");
        header("Content-Type: {$this->contentType}; charset=UTF-8");
        header("Content-Disposition: attachment; filename={$this->name}");
        header("Content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Cache-Control: private, max-age=0, must-revalidate");
        header("Pragma: public");
        header("Content-Length: {$this->size}");

        print $this->data;
    }
}