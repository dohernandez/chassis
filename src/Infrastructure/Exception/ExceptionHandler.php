<?php declare(strict_types=1);

namespace Chassis\Infrastructure\Exception;

use Symfony\Component\HttpFoundation\Response;

interface ExceptionHandler
{
    /**
     * @param \Throwable $throwable
     *
     * @return Response
     */
    public function __invoke(\Throwable $throwable): Response;
}
