<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP\Action;

use Symfony\Component\HttpFoundation\Request;

/**
 * Represents an HTTP action
 */
interface ActionInterface
{
    /**
     * @param Request $request
     * @param array $args
     *
     * @return mixed
     */
    public function __invoke(Request $request, array $args);
}
