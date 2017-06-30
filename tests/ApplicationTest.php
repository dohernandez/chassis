<?php declare(strict_types = 1);

namespace Tests\Chassis;

use Chassis\Infrastructure\Application;
use Chassis\Infrastructure\HTTP\Controller\ControllerInterface;
use Chassis\Infrastructure\HTTP\HTTPExceptionHandler;
use Chassis\Infrastructure\HTTP\Response\ResponseResolver;
use Chassis\Infrastructure\Routing\Route;
use Chassis\Infrastructure\Routing\RouteResolver;
use Closure;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationTest extends TestCase
{
    use MockHelpers;

    public function testShouldReturnSameContainerInstance()
    {
        $container = $this->mockContainer();
        $application = $this->createApplication($container);

        $this->assertSame($container, $application->getContainer());
    }

    /**
     * @param ContainerInterface|Closure|null $container
     *
     * @return Application
     */
    protected function createApplication($container = null): Application
    {
        if (is_null($container)) {
            $container = $this->mockContainer();
        } elseif ($container instanceof Closure) {
            $container = $this->mockContainer($container);
        }

        return new Application($container);
    }

    public function testGet()
    {
        $application = $this->createApplication();

        $route = $application->get('/', 'index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($route->getRoutePattern(), '/');
        $this->assertSame($route->getMethod(), 'GET');
        $this->assertSame($route->getHandle(), 'index');
    }

    public function testPut()
    {
        $application = $this->createApplication();

        $route = $application->put('/', 'index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($route->getRoutePattern(), '/');
        $this->assertSame($route->getMethod(), 'PUT');
        $this->assertSame($route->getHandle(), 'index');
    }

    public function testPost()
    {
        $application = $this->createApplication();

        $route = $application->post('/', 'index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($route->getRoutePattern(), '/');
        $this->assertSame($route->getMethod(), 'POST');
        $this->assertSame($route->getHandle(), 'index');
    }

    public function testPatch()
    {
        $application = $this->createApplication();

        $route = $application->patch('/', 'index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($route->getRoutePattern(), '/');
        $this->assertSame($route->getMethod(), 'PATCH');
        $this->assertSame($route->getHandle(), 'index');
    }

    public function testDelete()
    {
        $application = $this->createApplication();

        $route = $application->delete('/', 'index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($route->getRoutePattern(), '/');
        $this->assertSame($route->getMethod(), 'DELETE');
        $this->assertSame($route->getHandle(), 'index');
    }

    public function testGetLoogerShouldReturnSameLoggerInstance()
    {
        $logger = $this->mock(Logger::class);

        $application = $this->createApplication(function ($container) use ($logger) {
            $container->get('app.logger')->shouldBeCalled()->willReturn($logger);
        });

        $this->assertSame($logger, $application->getLooger());
    }

    public function testRunShouldReturnResponse()
    {
        $request = $this->mockRequest(function ($request) {
            $request->getPathInfo()->shouldBeCalled()->willReturn('/');
            $request->getMethod()->shouldBeCalled()->willReturn('GET');
        });

        $response = $this->mockResponse(function ($response) {
            $response->send()->shouldBeCalled()->willReturn($response);
        });

        $controller = new class($response) implements ControllerInterface {
            private $response;

            public function __construct(Response $response)
            {
                $this->response = $response;
            }

            public function __invoke(Request $request, string $action, array $pathParams): Response
            {
                return $this->response;
            }
        };

        $routeResolver = $this->mock(RouteResolver::class, function ($dispatcher) use ($controller) {
            $dispatcher->resolve('/', 'GET')->shouldBeCalled()->willReturn([
                $controller,
                'index',
                [],
            ]);
        });

        $application = $this->createApplication(function ($container) use ($routeResolver) {
            $container->has('app.route_resolver')->shouldBeCalled()->willReturn(true);
            $container->get('app.route_resolver')->shouldBeCalled()->willReturn($routeResolver);
        });

        $this->assertSame($response, $application->run($request));
    }

    public function testRunShouldReturnExceptionResponseDueNotRouteResolverNotFound()
    {
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

        $responseResolver = $this->mock(ResponseResolver::class, function ($responseResolver) use ($response) {
            $responseResolver->resolve(
                [ 'unique_code' => 'd7f1f4b8-5cd9-11e7-907b-a6006ad3dba0', 'message' => 'Route resolver is not defined.' ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            )->shouldBeCalled()->willReturn($response);
        });

        $exceptionHandler = new class($logger, $responseResolver) extends HTTPExceptionHandler {
            protected $errorId = 'd7f1f4b8-5cd9-11e7-907b-a6006ad3dba0';
        };

        $application = $this->createApplication(function ($container) use ($exceptionHandler) {
            $container->has('app.route_resolver')->shouldBeCalled()->willReturn(false);
            $container->get('app.http_exception_handler')->shouldBeCalled()->willReturn($exceptionHandler);
        });

        $this->assertSame($response, $application->run($request));
    }
}
