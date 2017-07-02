<?php declare(strict_types = 1);

namespace Tests\Chassis\Infrastructure\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Controller\Controller;
use Chassis\Infrastructure\HTTP\Response\ResponseResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Chassis\MockHelpers;

class ControllerTest extends TestCase
{
    use ControllerMockHelpers;
    use MockHelpers;

    public function testThatItReturnSameContainerInstance()
    {
        $container = $this->mockContainer();
        $controller = $this->createController($container);

        $this->assertSame($container, $controller->getContainer());
    }

    public function testThatItInvokeRespondResponse()
    {
        $action = 'index';

        $container = $this->mockContainer();
        $response = $this->mockResponse();

        $controller = $this->createController(
            $container,
            $this->getResponseResolverInit($response),
            $this->getEventDispatcherInit()
        );

        $request = $this->mockRequest($this->getRequestContentJsonInit());

        $controller->setTestCase($this);
        $controller->setRequest($request);
        $controller->setAction($action);
        $controller->setParams([]);

        $this->assertSame($response, $controller->__invoke($request, $action, []));
    }

    /**
     * @param ContainerInterface $container
     * @param callable $responseResolverInit
     * @param callable $eventDispatcherInit
     * @param mixed $runReturn
     *
     * @return Controller
     */
    private function createController(
        ContainerInterface $container,
        callable $responseResolverInit = null,
        callable $eventDispatcherInit = null,
        $runReturn = []
    ): Controller {
        $responseResolver = $this->mockResponseResolver($responseResolverInit);
        $eventDispatcher = $this->mockEventDispatcher($eventDispatcherInit);

        return new class($container, $responseResolver, $eventDispatcher, $runReturn) extends Controller {
            /*
             * @var TestCase
             */
            private $testCase = null;

            /*
             * @var Request
             */
            private $request = null;

            /*
             * @var string
             */
            private $action = null;

            /*
             * @var array
             */
            private $params = null;

            /**
             * @var mixed
             */
            private $runReturn;

            public function __construct(
                ContainerInterface $container,
                ResponseResolverInterface $responseResolver,
                EventDispatcherInterface $eventDispatcher,
                $runReturn
            ) {
                parent::__construct($container, $responseResolver, $eventDispatcher);

                $this->runReturn = $runReturn;
            }

            protected function run(Request $request, string $action, array $params)
            {
                $this->testCase->assertSame($this->request, $request);
                $this->testCase->assertSame($this->action, $action);
                $this->testCase->assertSame($this->params, $params);

                return $this->runReturn;
            }

            /**
             * @param TestCase $testCase
             */
            public function setTestCase(TestCase $testCase)
            {
                $this->testCase = $testCase;
            }

            /**
             * @param Request $request
             */
            public function setRequest(Request $request)
            {
                $this->request = $request;
            }

            /**
             * @param string $action
             */
            public function setAction(string $action)
            {
                $this->action = $action;
            }

            /**
             * @param array $params
             */
            public function setParams(array $params)
            {
                $this->params = $params;
            }
        };
    }

    public function testThatItInvokeRespondActionResponse()
    {
        $action = 'index';

        $container = $this->mockContainer();
        $response = $this->mockResponse();

        $controller = $this->createController(
            $container,
            null,
            $this->getEventDispatcherInit(),
            $response
        );

        $request = $this->mockRequest($this->getRequestContentJsonInit());

        $controller->setTestCase($this);
        $controller->setRequest($request);
        $controller->setAction($action);
        $controller->setParams([]);

        $this->assertSame($response, $controller->__invoke($request, $action, []));
    }

    public function testThatItInvokeRespondNoContentResponse()
    {
        $action = 'index';

        $container = $this->mockContainer();
        $response = $this->mockResponse();

        $controller = $this->createController(
            $container,
            function ($responseResolver) use ($response) {
                // here is the check where respond return a no content response
                $responseResolver->resolve('', Response::HTTP_NO_CONTENT)->shouldBeCalled()->willReturn($response);
            },
            $this->getEventDispatcherInit(),
            null
        );

        $request = $this->mockRequest($this->getRequestContentJsonInit());

        $controller->setTestCase($this);
        $controller->setRequest($request);
        $controller->setAction($action);
        $controller->setParams([]);

        $this->assertSame($response, $controller->__invoke($request, $action, []));
    }
}
