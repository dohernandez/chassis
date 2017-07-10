<?php declare(strict_types = 1);

namespace Tests\Chassis\Infrastructure;

use Chassis\Infrastructure\Application;
use Chassis\Infrastructure\HTTP\Controller\ControllerInterface;
use Chassis\Infrastructure\HTTP\HTTPExceptionHandler;
use Chassis\Infrastructure\HTTP\Response\ResponseResolverInterface;
use Closure;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ApplicationMockHelpers
{
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

    /**
     * @param Request $request
     * @param Response $response
     * @param string $action
     * @param array $pathParams
     *
     * @return mixed
     */
    protected function mockController(Request $request, Response $response, string $action, array $pathParams)
    {
        return new class($request, $response, $action, $pathParams) implements ControllerInterface {
            /**
             * @var TestCase
             */
            private $testCase;

            /*
             * @var Response
             */
            private $response;

            /**
             * @var string
             */
            private $action;

            /**
             * @var array
             */
            private $pathParams;

            /**
             * @var Request
             */
            private $request;

            public function __construct(Request $request, Response $response, string $action, array $pathParams)
            {
                $this->response = $response;
                $this->action = $action;
                $this->pathParams = $pathParams;
                $this->request = $request;
            }

            public function __invoke(Request $request, string $action, array $pathParams): Response
            {
                $this->testCase->assertSame($this->request, $request);
                $this->testCase->assertSame($this->action, $action);
                $this->testCase->assertSame($this->pathParams, $pathParams);

                return $this->response;
            }

            /**
             * @param TestCase $testCase
             */
            public function setTestCase(TestCase $testCase)
            {
                $this->testCase = $testCase;
            }
        };
    }

    /**
     * Creates a mock instance.
     *
     * @param string|null $classOrInterface
     * @param callable|null $init
     *
     * @return mixed
     */
    abstract protected function mock($classOrInterface = null, callable $init = null);

    /**
     * @param string $errorId
     * @param LoggerInterface $logger
     * @param ResponseResolverInterface $responseResolver
     *
     * @return HTTPExceptionHandler
     */
    protected function mockExceptionHandler(
        string $errorId,
        LoggerInterface $logger,
        ResponseResolverInterface $responseResolver
    ):HTTPExceptionHandler {
        $exceptionHandler = new class($errorId, $logger, $responseResolver) extends HTTPExceptionHandler {
            public function __construct(
                string $errorId,
                LoggerInterface $logger,
                ResponseResolverInterface $responseResolver
            ) {
                parent::__construct($logger, $responseResolver);

                $this->errorId = $errorId;
            }
        };

        return $exceptionHandler;
    }
}
