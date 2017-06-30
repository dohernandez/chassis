<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\Exception;

use Symfony\Component\HttpFoundation\Response;

class MethodNotAllowedHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param array      $allow    An array of allowed methods
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct(array $allow, $message = null, \Exception $previous = null, $code = 0)
    {
        $headers = ['Allow' => strtoupper(implode(', ', $allow))];

        parent::__construct(Response::HTTP_METHOD_NOT_ALLOWED, $message, $previous, $headers, $code);
    }
}
