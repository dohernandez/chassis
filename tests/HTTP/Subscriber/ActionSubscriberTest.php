<?php declare(strict_types = 1);

namespace Tests\Chassis\HTTP\Subscriber;

use Chassis\Infrastructure\HTTP\Event\AfterActionEvent;
use Chassis\Infrastructure\HTTP\Event\BeforeActionEvent;
use Chassis\Infrastructure\HTTP\Subscriber\ActionSubscriber;
use Closure;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ServerBag;
use Tests\Chassis\MockHelpers;

class ActionSubscriberTest extends TestCase
{
    use MockHelpers;

    public function testThatItCheckConstants()
    {
        $this->assertSame('X-Took', ActionSubscriber::HEADER_X_TOOK);
        $this->assertSame('X-Took-To-Action', ActionSubscriber::HEADER_X_TOOK_TO_ACTION);
        $this->assertSame('X-Took-Action', ActionSubscriber::HEADER_X_TOOK_ACTION);
        $this->assertSame(500, ActionSubscriber::PRIORITY_BEFORE_ACTION);
        $this->assertSame(-500, ActionSubscriber::PRIORITY_AFTER_ACTION);
    }

    public function testThatItCheckSubscriberEvents()
    {
        $subscriber = new ActionSubscriber();
        $this->assertSame(
            [
                BeforeActionEvent::NAME => ['beforeAction', ActionSubscriber::PRIORITY_BEFORE_ACTION],
                AfterActionEvent::NAME  => ['afterAction', ActionSubscriber::PRIORITY_AFTER_ACTION],
            ],
            $subscriber->getSubscribedEvents()
        );
    }

    public function testThatItTriggerEventSubscribed()
    {
        $subscriber = new ActionSubscriber();

        $timeRequest = microtime(true);
        $request = $this->mockRequest($this->getRequestInit($timeRequest));

        $beforeAction = $this->mock(BeforeActionEvent::class, function ($beforeAction) use ($request) {
            $beforeAction->getRequest()->shouldBeCalled()->willReturn($request);
        });

        $timeBeforeAction =  microtime(true);
        $subscriber->beforeAction($beforeAction, $timeBeforeAction);

        $timeAfterAction =  microtime(true);
        $response = $this->mockResponse($this->getResponseInit($timeRequest, $timeBeforeAction, $timeAfterAction));

        $afterAction = $this->mock(AfterActionEvent::class, function ($afterAction) use ($response) {
            $afterAction->getResponse()->shouldBeCalled()->willReturn($response);
        });

        $subscriber->afterAction($afterAction, $timeAfterAction);
    }

    /**
     * @param float $timeRequest
     *
     * @return Closure
     */
    private function getRequestInit(float $timeRequest): Closure
    {
        $server = $this->mock(ServerBag::class, function ($server) use ($timeRequest) {
            $server->get('REQUEST_TIME_FLOAT')->shouldBeCalled()->willReturn($timeRequest);
        });

        return function ($request) use ($server) {
            $request->server = $server;
        };
    }

    /**
     * @param float $timeRequest
     * @param float $timeBeforeAction
     * @param float $timeAfterAction
     *
     * @return Closure
     */
    private function getResponseInit(float $timeRequest, float $timeBeforeAction, float $timeAfterAction): Closure
    {
        $headerXTookToAction = $this->formatTime($timeBeforeAction - $timeRequest);
        $headerXTookAction = $this->formatTime($timeAfterAction - $timeBeforeAction);

        $header = $this->mock(HeaderBag::class, function ($header) use ($headerXTookToAction, $headerXTookAction) {
            $header->set(ActionSubscriber::HEADER_X_TOOK, Argument::containingString(' ms'))->shouldBeCalled();
            $header->set(ActionSubscriber::HEADER_X_TOOK_TO_ACTION, $headerXTookToAction)->shouldBeCalled();
            $header->set(ActionSubscriber::HEADER_X_TOOK_ACTION, $headerXTookAction)->shouldBeCalled();
        });

        return function ($response) use ($header) {
            $response->headers = $header;
        };
    }

    /**
     * @param float $time
     *
     * @return string
     */
    private function formatTime(float $time): string
    {
        return round($time * 1000, 3) . ' ms';
    }
}
