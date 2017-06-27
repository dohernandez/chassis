<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve a {@link Response} instance from data, status, and headers. Serialize the data when appropriate.
 */
interface ResponseResolverInterface
{
    /**
     * @param mixed $data
     * @param int $status
     * @param array $headers
     *
     * @return Response
     */
    public function resolve($data, int $status = Response::HTTP_OK, array $headers = []): Response;
}
