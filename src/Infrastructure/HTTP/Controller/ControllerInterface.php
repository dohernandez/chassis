<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Represents an HTTP controller
 */
interface ControllerInterface
{
    /**
     * @param Request $request
     * @param string $action
     * @param array $pathParams
     *
     * @return Response
     */
    public function __invoke(Request $request, string $action, array $pathParams): Response;
}
