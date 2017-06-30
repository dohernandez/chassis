<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\Routing;

use Chassis\Infrastructure\Exception\MethodNotAllowedHttpException;
use Chassis\Infrastructure\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

class RouteResolver implements RouteResolverInterface
{

    const CONTROLLER_METHOD_SEPARATOR = '#';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $httpMethod
     * @param string $uri
     *
     * @return array
     */
    public function resolve(string $httpMethod, string $uri): array
    {
        $dispatcher = $this->getDispatcher();

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        if ($routeInfo[0] == Dispatcher::METHOD_NOT_ALLOWED) {
            throw new NotFoundHttpException();
        }

        if ($routeInfo[0] == Dispatcher::METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedHttpException($routeInfo[1]);
        }

        list($controller, $method) = $this->getController();

        return [ $controller, $method, $routeInfo[2] ];
    }

    /**
     * @return Dispatcher
     */
    protected function getDispatcher(): Dispatcher
    {
        if ($this->getContainer()->has('app.request_dispatcher')) {
            return $this->getContainer()->has('app.request_dispatcher');
        }

        $routes = $this->routes;

        return \FastRoute\simpleDispatcher(function (RouteCollector $r) use ($routes) {
            foreach ($routes as $route) {
                $r->addRoute($route->getMethod(), $route->getRoutePattern(), $route->getHandle());
            }
        });
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }


    /**
     * @param string $action
     *
     * @return array
     */
    protected function getController(string $action = null): array
    {
        list($controller, $method) = explode(self::CONTROLLER_METHOD_SEPARATOR, $action) + [ 1 => 'index' ];

        $containerId = sprintf('app.controller[%s]', $controller);

        if ($this->getContainer()->has($containerId)) {
            return [ $this->getContainer()->get($containerId), $method ];
        }

        return [ $this->getContainer()->get('app.controller'), $method ];
    }
}
