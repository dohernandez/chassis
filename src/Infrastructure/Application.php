<?php declare(strict_types = 1);

namespace Chassis\Infrastructure;

use Chassis\Infrastructure\Exception\ExceptionHandler;
use Chassis\Infrastructure\HTTP\Controller\CommandController;
use Chassis\Infrastructure\Routing\Route;
use Chassis\Infrastructure\Routing\RouteResolverInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Application
{
    /**
     * @var Route[]
     */
    private $routes;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->routes = [];

        $this->container = $container;
    }

    /**
     * Maps a GET request
     *
     * @param string $pattern
     * @param mixed $action
     *
     * @return Route
     */
    public function get(string $pattern, $action)
    {
        return $this->addRoute(new Route('GET', $pattern, $action));
    }

    /**
     * Add route to the list
     *
     * @param Route $route
     *
     * @return Route
     */
    public function addRoute(Route $route)
    {
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Maps a POST request
     *
     * @param string $pattern
     * @param mixed $action
     *
     * @return Route
     */
    public function post(string $pattern, $action)
    {
        return $this->addRoute(new Route('POST', $pattern, $action));
    }

    /**
     * Maps a PUT request
     *
     * @param string $pattern
     * @param mixed $action
     *
     * @return Route
     */
    public function put(string $pattern, $action)
    {
        return $this->addRoute(new Route('PUT', $pattern, $action));
    }

    /**
     * Maps a PATCH request
     *
     * @param string $pattern
     * @param mixed $action
     *
     * @return Route
     */
    public function patch(string $pattern, $action)
    {
        return $this->addRoute(new Route('PATCH', $pattern, $action));
    }

    /**
     * Maps a DELETE request
     *
     * @param string $pattern
     * @param mixed $action
     *
     * @return Route
     */
    public function delete(string $pattern, $action)
    {
        return $this->addRoute(new Route('DELETE', $pattern, $action));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request = null): Response
    {
        try {
            $request = $this->getRequest($request);
            $response = $this->handleRequest($request);
        } catch (Throwable $throwable) {
            $response = $this->handleException($throwable);
        }

        return $response->send();
    }

    /**
     * @return LoggerInterface
     */
    public function getLooger(): LoggerInterface
    {
        return $this->getContainer()->get('app.logger');
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param Request|null $request
     * @return Request
     */
    protected function getRequest(Request $request = null): Request
    {
        if (null === $request) {
            $request = Request::createFromGlobals();
        }

        return $request;
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function handleRequest(Request $request): Response
    {
        $routeResolver = $this->getRouteResolver();

        return $this->process($routeResolver, $request);
    }

    /**
     * @return RouteResolverInterface
     */
    protected function getRouteResolver(): RouteResolverInterface
    {
        if ($this->getContainer()->has('app.route_resolver')) {
            return $this->getContainer()->get('app.route_resolver');
        }

        throw new \LogicException('Route resolver is not defined.');
    }

    /**
     * @param RouteResolverInterface $RouteResolver
     * @param Request $request
     * @return Response
     */
    protected function process(RouteResolverInterface $RouteResolver, Request $request): Response
    {
        list($controller, $action, $params) = $RouteResolver->resolve(rawurldecode($request->getPathInfo()), $request->getMethod());

        if ($controller instanceof CommandController) {
            return $controller->__invoke($request, $action, $params);
        }

        return $controller->__invoke($request, $action, $params);
    }

    protected function handleException(Throwable $throwable)
    {
        /* @var ExceptionHandler $handler */
        $handler = $this->container->get('app.http_exception_handler');

        return $handler->__invoke($throwable);
    }
}
