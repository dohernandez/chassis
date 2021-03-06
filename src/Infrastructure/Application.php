<?php declare(strict_types = 1);

namespace Chassis\Infrastructure;

use Chassis\Infrastructure\Exception\ExceptionHandlerInterface;
use Chassis\Infrastructure\HTTP\Controller\CommandController;
use Chassis\Infrastructure\Routing\Route;
use Chassis\Infrastructure\Routing\RouteResolverInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use UnexpectedValueException;

class Application
{
    use MiddlewareAwareTrait;

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
     * Add middleware
     *
     * @param string|callable $callable
     *
     * @return static
     */
    public function add($callable)
    {
        return $this->addMiddleware(new DeferredCallable($callable, $this->container));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request = null): Response
    {
        try {
            $request = $this->getRequest($request);

            $response = $this->callMiddlewareStack($request);
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
    public function __invoke(Request $request): Response
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
            $routeResolver = $this->getContainer()->get('app.route_resolver');

            if ($routeResolver instanceof RouteResolverInterface === false) {
                throw new UnexpectedValueException(
                    sprintf('The route resolver class must be an instance of %s', RouteResolverInterface::class)
                );
            }

            $routeResolver->setRoutes($this->routes);

            return $routeResolver;
        }

        throw new \LogicException('Route resolver is not defined.');
    }

    /**
     * @param RouteResolverInterface $routeResolver
     * @param Request $request
     * @return Response
     */
    protected function process(RouteResolverInterface $routeResolver, Request $request): Response
    {
        list($controller, $action, $params) = $routeResolver->resolve($request->getMethod(), rawurldecode($request->getPathInfo()));

        if ($controller instanceof CommandController) {
            return $controller($request, $action, $params);
        }

        return $controller($request, $action, $params);
    }

    protected function handleException(Throwable $throwable)
    {
        /* @var ExceptionHandlerInterface $handler */
        $handler = $this->container->get('app.http_exception_handler');

        return $handler($throwable);
    }
}
