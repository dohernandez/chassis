<?php declare(strict_types = 1);

namespace Tests\Chassis\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Action\Action;
use Chassis\Infrastructure\HTTP\Action\ActionInterface;
use Chassis\Infrastructure\HTTP\Controller\ActionController;
use Chassis\Infrastructure\HTTP\Event\BeforeActionEvent;
use LogicException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Chassis\MockHelpers;

class ActionControllerTest extends TestCase
{
    use ControllerMockHelpers;
    use MockHelpers;

    public function testThatItInvokeAction()
    {
        $routeAction = 'index';

        $request = $this->mockRequest($this->getRequestContentJsonInit());
        $action = $this->createAction();

        $action->setTestCase($this);
        $action->setRequest($request);
        $action->setArgs([]);

        $container = $this->mockContainer(function ($container) use ($routeAction, $action) {
            $container->get($routeAction)->shouldBeCalled()->willReturn($action);
        });
        $response = $this->mockResponse();
        $responseResolver = $this->mockResponseResolver($this->getResponseResolverInit($response));
        $eventDispatcher = $this->mockEventDispatcher($this->getEventDispatcherInit());

        $controller = new ActionController($container, $responseResolver, $eventDispatcher);

        $this->assertSame($response, $controller->__invoke($request, $routeAction, []));
    }

    /**
     * @param Response $response
     *
     * @return ActionInterface
     */
    private function createAction(): ActionInterface
    {
        return new class() implements ActionInterface {
            /*
             * @var TestCase
             */
            private $testCase = null;
            /*
             * @var Request
             */
            private $request = null;
            /*
             * @var array
             */
            private $args = null;

            public function __invoke(Request $request, array $args)
            {
                $this->testCase->assertSame($this->request, $request);
                $this->testCase->assertSame($this->args, $args);

                return [];
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
             * @param array $args
             */
            public function setArgs(array $args)
            {
                $this->args = $args;
            }
        };
    }

    public function testThatItThrowAnExceptionIfActionNotImplementActionInterface()
    {
        $routeAction = 'index';

        $request = $this->mockRequest($this->getRequestContentJsonInit());
        $container = $this->mockContainer(function ($container) use ($routeAction) {
            $container->get($routeAction)->shouldBeCalled()->willReturn(new stdClass());
        });
        $responseResolver = $this->mockResponseResolver();
        $eventDispatcher = $this->mockEventDispatcher(function ($eventDispatcher) {
            $eventDispatcher->dispatch(
                BeforeActionEvent::NAME,
                Argument::type(BeforeActionEvent::class)
            )->shouldBeCalled();
        });

        $controller = new ActionController($container, $responseResolver, $eventDispatcher);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('Action `%s` must extends `%s`', stdClass::class, Action::class));

        $controller->__invoke($request, $routeAction, []);
    }
}
