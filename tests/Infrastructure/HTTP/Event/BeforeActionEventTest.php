<?php declare(strict_types = 1);

namespace Tests\Chassis\Infrastructure\HTTP\Event;

use Chassis\Infrastructure\HTTP\Event\BeforeActionEvent;
use PHPUnit\Framework\TestCase;
use Tests\Chassis\MockHelpers;

class BeforeActionEventTest extends TestCase
{
    use MockHelpers;

    public function testThatItCheckNameConstant()
    {
        $nameConstants = 'http.before.action';

        $this->assertSame($nameConstants, BeforeActionEvent::NAME);
    }

    public function testThatItCreateAnEvent()
    {
        $action = 'index';
        $request = $this->mockRequest();

        $event = new BeforeActionEvent($action, $request);

        $this->assertSame($action, $event->getAction());
        $this->assertSame($request, $event->getRequest());
    }
}
