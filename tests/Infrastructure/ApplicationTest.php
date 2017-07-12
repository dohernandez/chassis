<?php declare(strict_types = 1);

namespace Tests\Chassis\Infrastructure;

use Chassis\Infrastructure\Exception\NotFoundException;
use Chassis\Infrastructure\Routing\Route;
use Chassis\Infrastructure\Routing\RouteResolverInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\Chassis\MockHelpers;

class ApplicationTest extends TestCase
{
    use ApplicationMockHelpers;
    use MockHelpers;

    public function testThatItReturnSameContainerInstance()
    {
        $container = $this->mockContainer();
        $application = $this->createApplication($container);

        $this->assertSame($container, $application->getContainer());
    }

    public function testThatItAddRouteMethodGet()
    {
        $application = $this->createApplication();

        $route = $application->get('/', 'index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($route->getRoutePattern(), '/');
        $this->assertSame($route->getMethod(), 'GET');
        $this->assertSame($route->getHandle(), 'index');
    }

    public function testThatItAddRouteMethodPut()
    {
        $application = $this->createApplication();

        $route = $application->put('/', 'index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($route->getRoutePattern(), '/');
        $this->assertSame($route->getMethod(), 'PUT');
        $this->assertSame($route->getHandle(), 'index');
    }

    public function testThatItAddRouteMethodPost()
    {
        $application = $this->createApplication();

        $route = $application->post('/', 'index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($route->getRoutePattern(), '/');
        $this->assertSame($route->getMethod(), 'POST');
        $this->assertSame($route->getHandle(), 'index');
    }

    public function testThatItAddRouteMethodPatch()
    {
        $application = $this->createApplication();

        $route = $application->patch('/', 'index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($route->getRoutePattern(), '/');
        $this->assertSame($route->getMethod(), 'PATCH');
        $this->assertSame($route->getHandle(), 'index');
    }

    public function testThatItAddRouteMethodDelete()
    {
        $application = $this->createApplication();

        $route = $application->delete('/', 'index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($route->getRoutePattern(), '/');
        $this->assertSame($route->getMethod(), 'DELETE');
        $this->assertSame($route->getHandle(), 'index');
    }

    public function testThatItReturnSameLoggerInstance()
    {
        $logger = $this->mock(Logger::class);

        $application = $this->createApplication(function ($container) use ($logger) {
            $container->get('app.logger')->shouldBeCalled()->willReturn($logger);
        });

        $this->assertSame($logger, $application->getLooger());
    }

    public function testThatItReturnResponse()
    {
        $action = $this->getAction();
        $endpoint = $this->getEndPoint();
        $method = $this->getMethod();
        $pathParams = [];
        $route = $this->createRoute($method, $endpoint, $action);

        $request = $this->mockRequest(function ($request) use ($endpoint, $method) {
            $request->getPathInfo()->shouldBeCalled()->willReturn($endpoint);
            $request->getMethod()->shouldBeCalled()->willReturn($method);
        });

        $response = $this->mockResponse(function ($response) {
            $response->send()->shouldBeCalled()->willReturn($response);
        });

        $controller = $this->mockController($request, $response, $action, $pathParams);
        $controller->setTestCase($this);

        $routeResolver = $this->mockRouteResolver($this->getRouteResolverInit(
            $controller,
            $action,
            $endpoint,
            $method,
            $pathParams,
            $route
        ));

        $application = $this->createApplication(function ($container) use ($routeResolver) {
            $container->has('app.route_resolver')->shouldBeCalled()->willReturn(true);
            $container->get('app.route_resolver')->shouldBeCalled()->willReturn($routeResolver);
        });

        $application->addRoute($route);

        $this->assertSame($response, $application->run($request));
    }

    public function testThatItThrowAnExceptionResponseDueNotRouteResolverNotFound()
    {
        $errorId = uniqid();
        $errorMessage = 'Route resolver is not defined.';

        $request = $this->mockRequest();

        $logger = $this->mockLogger($errorId, $errorMessage);

        $response = $this->mockResponse(function ($response) {
            $response->send()->shouldBeCalled()->willReturn($response);
        });

        $responseResolver = $this->mockResponseResolver($response, $errorId, $errorMessage);

        $exceptionHandler = $this->mockExceptionHandler($errorId, $logger, $responseResolver);

        $application = $this->createApplication(function ($container) use ($exceptionHandler) {
            $container->has('app.route_resolver')->shouldBeCalled()->willReturn(false);
            $container->get('app.http_exception_handler')->shouldBeCalled()->willReturn($exceptionHandler);
        });

        $this->assertSame($response, $application->run($request));
    }

    public function testThatItThrowAnExceptionDueRouteResolverInstanceNotImplementRouteResolverInterface()
    {
        $errorId = uniqid();
        $errorMessage = sprintf(
            'The route resolver class must be an instance of %s',
            RouteResolverInterface::class
        );
        $request = $this->mockRequest();

        $response = $this->mockResponse(function ($response) {
            $response->send()->shouldBeCalled()->willReturn($response);
        });

        $logger = $this->mockLogger($errorId, $errorMessage);
        $responseResolver = $this->mockResponseResolver($response, $errorId, $errorMessage);
        $exceptionHandler = $this->mockExceptionHandler($errorId, $logger, $responseResolver);

        $application = $this->createApplication(function ($container) use ($exceptionHandler) {
            $container->has('app.route_resolver')->shouldBeCalled()->willReturn(true);
            $container->get('app.route_resolver')->shouldBeCalled()->willReturn(new stdClass());

            $container->get('app.http_exception_handler')->shouldBeCalled()->willReturn($exceptionHandler);
        });

        $this->assertSame($response, $application->run($request));
    }

    public function testThatItThrowAnExceptionDueRouteNotFound()
    {
        $action = $this->getAction();
        $errorId = uniqid();
        $errorMessage = 'Resource not found.';
        $endpoint = $this->getEndPoint();
        $method = $this->getMethod();
        $route = $this->createRoute($method, $endpoint, $action);

        $request = $this->mockRequest(function ($request) use ($endpoint, $method) {
            $request->getPathInfo()->shouldBeCalled()->willReturn($endpoint);
            $request->getMethod()->shouldBeCalled()->willReturn($method);
        });

        $response = $this->mockResponse(function ($response) {
            $response->send()->shouldBeCalled()->willReturn($response);
        });

        $logger = $this->mockLogger($errorId, $errorMessage);
        $responseResolver = $this->mockResponseResolver($response, $errorId, $errorMessage, Response::HTTP_NOT_FOUND);
        $exceptionHandler = $this->mockExceptionHandler($errorId, $logger, $responseResolver);

        $routeResolver = $this->mockRouteResolver(
            function ($routeResolver) use ($endpoint, $method, $route) {
                $routeResolver->resolve($method, $endpoint)->shouldBeCalled()->willThrow(new NotFoundException());

                $routeResolver->setRoutes([ $route ])->shouldBeCalled();
            }
        );

        $application = $this->createApplication(function ($container) use ($exceptionHandler, $routeResolver) {
            $container->has('app.route_resolver')->shouldBeCalled()->willReturn(true);
            $container->get('app.route_resolver')->shouldBeCalled()->willReturn($routeResolver);

            $container->get('app.http_exception_handler')->shouldBeCalled()->willReturn($exceptionHandler);
        });

        $application->addRoute($route);

        $this->assertSame($response, $application->run($request));
    }

    public function testThatItAddMiddlewareToTheApplication()
    {
        $action = $this->getAction();
        $endpoint = $this->getEndPoint();
        $method = $this->getMethod();
        $pathParams = [];
        $route = $this->createRoute($method, $endpoint, $action);

        $request = $this->mockRequest(function ($request) use ($endpoint, $method) {
            $request->getPathInfo()->shouldBeCalled()->willReturn($endpoint);
            $request->getMethod()->shouldBeCalled()->willReturn($method);
        });

        $response = $this->mockResponse(function ($response) {
            $response->send()->shouldBeCalled()->willReturn($response);
        });

        $controller = $this->mockController($request, $response, $action, $pathParams);
        $controller->setTestCase($this);

        $routeResolver = $this->mockRouteResolver($this->getRouteResolverInit(
            $controller,
            $action,
            $endpoint,
            $method,
            $pathParams,
            $route
        ));

        $application = $this->createApplication(function ($container) use ($routeResolver) {
            $container->has('app.route_resolver')->shouldBeCalled()->willReturn(true);
            $container->get('app.route_resolver')->shouldBeCalled()->willReturn($routeResolver);
        });
        $application->addRoute($route);

        $testCase = $this;
        $application->add(function ($middleWareRequest, $middleWareResponse, $next) use (
            $request,
            $response,
            $testCase
        ) {
            $testCase->assertSame($request, $middleWareRequest);
            $testCase->assertNull($middleWareResponse);
            $middleWareResponse = $next($middleWareRequest, $middleWareResponse);
            $testCase->assertSame($response, $middleWareResponse);

            return $middleWareResponse;
        });

        $this->assertSame($response, $application->run($request));
    }

    public function testThatItThrowAnExceptionMiddlewareNotReturnResponseToTheApplication()
    {
        $errorId = uniqid();
        $errorMessage = sprintf('Middleware must return instance of %s', Response::class);
        $action = $this->getAction();
        $endpoint = $this->getEndPoint();
        $method = $this->getMethod();
        $pathParams = [];
        $route = $this->createRoute($method, $endpoint, $action);

        $request = $this->mockRequest(function ($request) use ($endpoint, $method) {
            $request->getPathInfo()->shouldBeCalled()->willReturn($endpoint);
            $request->getMethod()->shouldBeCalled()->willReturn($method);
        });

        $response = $this->mockResponse(function ($response) {
            $response->send()->shouldBeCalled()->willReturn($response);
        });

        $controller = $this->mockController($request, $response, $action, $pathParams);
        $controller->setTestCase($this);

        $routeResolver = $this->mockRouteResolver($this->getRouteResolverInit(
            $controller,
            $action,
            $endpoint,
            $method,
            $pathParams,
            $route
        ));

        $logger = $this->mockLogger($errorId, $errorMessage);
        $responseResolver = $this->mockResponseResolver($response, $errorId, $errorMessage);
        $exceptionHandler = $this->mockExceptionHandler($errorId, $logger, $responseResolver);

        $application = $this->createApplication(function ($container) use ($routeResolver, $exceptionHandler) {
            $container->has('app.route_resolver')->shouldBeCalled()->willReturn(true);
            $container->get('app.route_resolver')->shouldBeCalled()->willReturn($routeResolver);

            $container->get('app.http_exception_handler')->shouldBeCalled()->willReturn($exceptionHandler);

        });
        $application->addRoute($route);

        $application->add(function ($middleWareRequest, $middleWareResponse, $next) {
            $next($middleWareRequest, $middleWareResponse);
        });

        $this->assertSame($response, $application->run($request));
    }
}
