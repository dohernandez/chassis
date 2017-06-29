<?php declare(strict_types = 1);

namespace Chassis\Application\Exception;

class HttpException extends \RuntimeException implements HttpExceptionInterface
{
    /*
     * @var int
     */
    protected $statusCode;

    /*
     * @var array
     */
    protected $headers;

    /**
     * @param int $statusCode
     * @param string|null $message
     * @param \Exception|null $previous
     * @param array $headers
     * @param int $code
     */
    public function __construct(int $statusCode, $message = null, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set response headers.
     *
     * @param array $headers Response headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
}
