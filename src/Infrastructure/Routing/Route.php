<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\Routing;

final class Route
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $routePattern;

    /**
     * @var string
     */
    private $handle;

    public function __construct(string $method, string $routePattern, string $handle)
    {
        $this->method = $method;
        $this->routePattern = $routePattern;
        $this->handle = $handle;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getRoutePattern()
    {
        return $this->routePattern;
    }

    /**
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }
}
