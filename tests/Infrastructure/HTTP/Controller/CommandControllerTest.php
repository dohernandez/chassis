<?php declare(strict_types = 1);

namespace Tests\Chassis\Infrastructure\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Controller\CommandController;
use Chassis\Infrastructure\HTTP\Controller\Controller;
use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\Chassis\MockHelpers;

class CommandControllerTest extends TestCase
{
    use ControllerMockHelpers;
    use MockHelpers;

    public function testThatItInvokeFunctionAction()
    {
        $routeAction = 'index';
        $args = [ 'action_test' => true ];
        $response = $this->mockResponse();

        $controller = $this->createController($response);

        $controller->setTestCase($this);

        $request = $this->mockRequest($this->getRequestContentJsonInit());
        $this->assertSame($response, $controller($request, $routeAction, $args));
    }

    /**
     * @param Response $response
     * @param callable|null $commandBusInit
     *
     * @return Controller
     */
    protected function createController(
        Response $response,
        callable $commandBusInit = null
    ): Controller {
        $commandBus = $this->mock(CommandBus::class, $commandBusInit);
        $container = $this->mockContainer();
        $responseResolver = $this->mockResponseResolver($this->getResponseResolverInit($response));
        $eventDispatcher = $this->mockEventDispatcher($this->getEventDispatcherInit());

        return new class($container, $responseResolver, $eventDispatcher, $commandBus) extends CommandController {
            /*
             * @var TestCase
             */
            private $testCase = null;

            public function index(bool $actionTest)
            {
                $this->testCase->assertTrue($actionTest);

                return [];
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
}
