<?php declare(strict_types = 1);

namespace Chassis\Infrastructure;

use Chassis\Infrastructure\HTTP\Controller\Controller;
use Chassis\Infrastructure\Routing\Route;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;


class Application
{

    /**
     * @var Route[]
     */
    protected $routes;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @param ContainerBuilder $builder
     */
    public function __construct(ContainerBuilder $builder)
    {
        $this->routes = [];

        $this->container = $builder->build();
    }

    /**
     * Maps a GET request
     * @param string $pattern
     * @param mixed $to
     */
    public function get(string $pattern, $to)
    {
        $this->addRoute(new Route('GET', $pattern, $to));
    }

    /**
     * Add route to the list
     * @param Route $route
     */
    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * Maps a POST request
     * @param string $pattern
     * @param mixed $to
     */
    public function post(string $pattern, $to)
    {
        $this->addRoute(new Route('POST', $pattern, $to));
    }

    /**
     * Maps a PUT request
     * @param string $pattern
     * @param mixed $to
     */
    public function put(string $pattern, $to)
    {
        $this->addRoute(new Route('PUT', $pattern, $to));
    }

    /**
     * Maps a PATCH request
     * @param string $pattern
     * @param mixed $to
     */
    public function patch(string $pattern, $to)
    {
        $this->addRoute(new Route('PATCH', $pattern, $to));
    }

    /**
     * Maps a DELETE request
     * @param string $pattern
     * @param mixed $to
     */
    public function delete(string $pattern, $to)
    {
        $this->addRoute(new Route('DELETE', $pattern, $to));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request = null): Response
    {
        $request = $this->getRequest($request);

        $this->getLooger()->debug(sprintf('Requesting the end point %s', $request->getUri()));

        $response = $this->dispatchRequest($request);

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
    protected function dispatchRequest(Request $request): Response
    {
        $dispatcher = $this->getDispatcher();

        return $this->handle($dispatcher, $request);
    }

    /**
     * @return Dispatcher
     */
    protected function getDispatcher(): Dispatcher
    {
        $routes = $this->routes;

        $dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) use ($routes) {
            foreach ($routes as $route) {
                $r->addRoute($route->getMethod(), $route->getRoutePattern(), $route->getHandle());
            }
        });

        return $dispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     * @param Request $request
     * @return Response
     */
    protected function handle(Dispatcher $dispatcher, Request $request): Response
    {
        $uri = rawurldecode($request->getPathInfo());
        $method = $request->getMethod();

        $routeInfo = $dispatcher->dispatch($method, $uri);

        if ($routeInfo[0] == Dispatcher::METHOD_NOT_ALLOWED) {
            return new Response('', Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($routeInfo[0] == Dispatcher::FOUND) {
            $controller = $this->getController($routeInfo[1]);

            return $controller->__invoke($request, $routeInfo[1], $routeInfo[2]);

        }

        return new Response('', Response::HTTP_NOT_FOUND);
    }

    /**
     * @param string $action
     *
     * @return Controller
     */
    protected function getController(string $action = null): Controller
    {
        $containerId = $action ? sprintf('app.controller[%s]', $action) : 'app.controller';

        return $this->getContainer()->get($containerId);
    }
}
