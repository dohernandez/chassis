<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\Routing;

/**
 * Resolve an application {@link Route}.
 */
interface RouteResolverInterface
{
    /**
     * @param string $httpMethod
     * @param string $uri
     *
     * @return array
     */
    public function resolve(string $httpMethod, string $uri): array;
}
