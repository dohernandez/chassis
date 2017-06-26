<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Represents an HTTP action
 */
interface ActionInterface
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args) : Response;
}
