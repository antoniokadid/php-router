<?php

namespace Router;

use Throwable;

/**
 * Class RouterException
 *
 * @package Router
 */
class RouterException extends \Exception
{
    /** @var array */
    private $parameters;

    /**
     * RouterException constructor.
     *
     * @param string $message
     * @param int $code
     * @param array $parameters
     * @param Throwable|NULL $previous
     */
    public function __construct(string $message = '', int $code = 0, array $parameters = [], Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);

        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}