<?php declare(strict_types = 1);

namespace Tests\Chassis\Infrastructure;

use Chassis\Infrastructure\HTTP\Response\ResponseResolver;
use Chassis\Infrastructure\Routing\Route;
use Chassis\Infrastructure\Routing\RouteResolverInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
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
        $action = 'index';
        $endpoint = '/';
        $method = 'GET';
        $pathParams = [];
        $route = new Route($method, $endpoint, $action);

        $request = $this->mockRequest(function ($request) use ($endpoint, $method) {
            $request->getPathInfo()->shouldBeCalled()->willReturn($endpoint);
            $request->getMethod()->shouldBeCalled()->willReturn($method);
        });

        $response = $this->mockResponse(function ($response) {
            $response->send()->shouldBeCalled()->willReturn($response);
        });

        $controller = $this->mockController($request, $response, $action, $pathParams);

        $controller->setTestCase($this);

        $routeResolver = $this->mock(
            RouteResolverInterface::class,
            function ($routeResolver) use ($controller, $action, $endpoint, $method, $pathParams, $route) {
                $routeResolver->resolve($method, $endpoint)->shouldBeCalled()->willReturn([
                    $controller,
                    $action,
                    $pathParams,
                ]);

                $routeResolver->setRoutes([ $route ])->shouldBeCalled();
            }
        );

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

        $logger = $this->mock(Logger::class, function ($logger) {
            $logger->log(
                LogLevel::ERROR,
                'Route resolver is not defined.',
                [ 'unique_code'=> 'd7f1f4b8-5cd9-11e7-907b-a6006ad3dba0']
            );
        });

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

        $logger = $this->mock(Logger::class, function ($logger) use ($errorId) {
            $logger->log(
                LogLevel::ERROR,
                'Route resolver is not defined.',
                [ 'unique_code'=> $errorId]
            );
        });

        $responseResolver = $this->mockResponseResolver($response, $errorId, $errorMessage);
        $exceptionHandler = $this->mockExceptionHandler($errorId, $logger, $responseResolver);

        $application = $this->createApplication(function ($container) use ($exceptionHandler) {
            $container->has('app.route_resolver')->shouldBeCalled()->willReturn(true);
            $container->get('app.route_resolver')->shouldBeCalled()->willReturn(new stdClass());

            $container->get('app.http_exception_handler')->shouldBeCalled()->willReturn($exceptionHandler);
        });

        $this->assertSame($response, $application->run($request));
    }

    /**
     * @param Response $response
     * @param string $errorId
     * @param string $message
     * @param int $status
     *
     * @return ResponseResolver
     */
    private function mockResponseResolver(
        Response $response,
        string $errorId,
        string $message,
        int $status = Response::HTTP_INTERNAL_SERVER_ERROR
    ): ResponseResolver {
        $responseResolver = $this->mock(
            ResponseResolver::class,
            function ($responseResolver) use ($response, $errorId, $message, $status) {
                $responseResolver->resolve(
                    [ 'unique_code' => $errorId, 'message' => $message ],
                    $status
                )->shouldBeCalled()->willReturn($response);
            }
        );

        return $responseResolver;
    }
}
