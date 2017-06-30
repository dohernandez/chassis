<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\Exception;

use Symfony\Component\HttpFoundation\Response;

class NotFoundHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(Response::HTTP_NOT_FOUND, $message, $previous, [], $code);
    }
}
