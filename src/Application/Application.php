<?php declare(strict_types = 1);

namespace Chassis\Application;

use Chassis\Application\Routing\Route;
use Chassis\Presentation\HTTP\Action;
use Chassis\Presentation\HTTP\ActionInterface;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Application
{
    /**
     * @var Route[]
     */
    protected $routes;

    public function __construct()
    {
        $this->routes = [];
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

        $response = $this->dispatchRequest($request);

        return $response->send();
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
            $action = $this->resolveAction($routeInfo[1]);
            $vars = $routeInfo[2];

            if ($action instanceof ActionInterface) {
                return $action->__invoke($request, new Response('', Response::HTTP_OK), $vars);
            }

        }

        return new Response('', Response::HTTP_NOT_FOUND);
    }

    /**
     * @param string $action Action identifier.
     *
     * @return ActionInterface
     */
    public function resolveAction(string $action): ActionInterface
    {
//        $controller = $this->container->get($id);

        $action = new $action();

        if (!$action instanceof ActionInterface) {
            throw new \LogicException("Action `$action` must implement `" . Action::class . "`.");
        }

        return $action;
    }
}
