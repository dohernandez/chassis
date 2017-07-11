<?php declare(strict_types = 1);

namespace Tests\Chassis\Infrastructure\Routing;

use Chassis\Infrastructure\Exception\MethodNotAllowedHttpException;
use Chassis\Infrastructure\Exception\NotFoundException;
use Chassis\Infrastructure\HTTP\Controller\ControllerInterface;
use Chassis\Infrastructure\Routing\RouteResolver;
use Closure;
use FastRoute\Dispatcher;
use PHPUnit\Framework\TestCase;
use Tests\Chassis\MockHelpers;

class RouteResolverTest extends TestCase
{
    use MockHelpers;

    public function testThatItCheckConstants()
    {
        $this->assertSame('#', RouteResolver::CONTROLLER_METHOD_SEPARATOR);
        $this->assertSame('index', RouteResolver::CONTROLLER_DEFAULT_METHOD);
    }

    public function testThatItReturnSameContainerInstance()
    {
        $container = $this->mockContainer();
        $application = $this->createRouteResolver($container);

        $this->assertSame($container, $application->getContainer());
    }

    /**
     * @param ContainerInterface|Closure|null $container
     *
     * @return RouteResolver
     */
    private function createRouteResolver($container = null): RouteResolver
    {
        if (is_null($container)) {
            $container = $this->mockContainer();
        } elseif ($container instanceof Closure) {
            $container = $this->mockContainer($container);
        }

        return new RouteResolver($container);
    }

    public function testThatItReturnControllerInstanceAndDefaultIndexMethod()
    {
        $httpMethod = 'GET';
        $uri = '/';

        $dispatcher = $this->mock(Dispatcher::class, function ($dispatcher) use ($httpMethod, $uri) {
            $dispatcher->dispatch($httpMethod, $uri)->shouldBeCalled()->willReturn([ Dispatcher::FOUND, '', [] ]);
        });
        $controller = $this->mock(ControllerInterface::class);
        $routeResolver = $this->createRouteResolver(
            function ($container) use ($dispatcher, $controller) {
                $container->has('app.request_dispatcher')->shouldBeCalled()->willReturn(true);
                $container->get('app.request_dispatcher')->shouldBeCalled()->willReturn($dispatcher);

                $container->has('app.controller[]')->shouldBeCalled()->willReturn(false);
                $container->get('app.controller')->shouldBeCalled()->willReturn($controller);
            }
        );

        $this->assertSame([ $controller, 'index', [] ], $routeResolver->resolve($httpMethod, $uri));
    }

    public function testThatItReturnControllerInstanceAndActionMethod()
    {
        $httpMethod = 'POST';
        $uri = '/create';
        $containerId = 'app.controller[Action]';

        $dispatcher = $this->mock(Dispatcher::class, function ($dispatcher) use ($httpMethod, $uri) {
            $dispatcher->dispatch($httpMethod, $uri)->shouldBeCalled()->willReturn([
                Dispatcher::FOUND,
                'Action#create',
                [],
            ]);
        });
        $controller = $this->mock(ControllerInterface::class);
        $routeResolver = $this->createRouteResolver(
            function ($container) use ($dispatcher, $containerId, $controller) {
                $container->has('app.request_dispatcher')->shouldBeCalled()->willReturn(true);
                $container->get('app.request_dispatcher')->shouldBeCalled()->willReturn($dispatcher);

                $container->has($containerId)->shouldBeCalled()->willReturn(true);
                $container->get($containerId)->shouldBeCalled()->willReturn($controller);
            }
        );

        $this->assertSame([ $controller, 'create', [] ], $routeResolver->resolve($httpMethod, $uri));
    }

    public function testThatItThrowNotFoundException()
    {
        $httpMethod = 'GET';
        $uri = '/';

        $dispatcher = $this->mock(Dispatcher::class, function ($dispatcher) use ($httpMethod, $uri) {
            $dispatcher->dispatch($httpMethod, $uri)->shouldBeCalled()->willReturn([ Dispatcher::NOT_FOUND ]);
        });
        $routeResolver = $this->createRouteResolver(
            function ($container) use ($dispatcher) {
                $container->has('app.request_dispatcher')->shouldBeCalled()->willReturn(true);
                $container->get('app.request_dispatcher')->shouldBeCalled()->willReturn($dispatcher);
            }
        );

        $this->expectException(NotFoundException::class);

        $routeResolver->resolve($httpMethod, $uri);
    }

    public function testThatItThrowMethodNotAllowedHttpException()
    {
        $httpMethod = 'POST';
        $uri = '/create';
        $action = 'Action#create';

        $dispatcher = $this->mock(Dispatcher::class, function ($dispatcher) use ($httpMethod, $uri, $action) {
            $dispatcher->dispatch($httpMethod, $uri)->shouldBeCalled()->willReturn([
                Dispatcher::METHOD_NOT_ALLOWED,
                [ 'GET' ],
                [],
            ]);
        });
        $routeResolver = $this->createRouteResolver(
            function ($container) use ($dispatcher, $action) {
                $container->has('app.request_dispatcher')->shouldBeCalled()->willReturn(true);
                $container->get('app.request_dispatcher')->shouldBeCalled()->willReturn($dispatcher);
            }
        );

        $this->expectException(MethodNotAllowedHttpException::class);

        $routeResolver->resolve($httpMethod, $uri);
    }
}
