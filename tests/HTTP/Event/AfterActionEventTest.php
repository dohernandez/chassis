<?php declare(strict_types = 1);

namespace Tests\Chassis\HTTP\Event;

use Chassis\Infrastructure\HTTP\Event\AfterActionEvent;
use PHPUnit\Framework\TestCase;
use Tests\Chassis\MockHelpers;

class AfterActionEventTest extends TestCase
{
    use MockHelpers;

    public function testThatItCheckNameConstant()
    {
        $nameConstants = 'http.after.action';

        $this->assertSame($nameConstants, AfterActionEvent::NAME);
    }

    public function testThatItCreateAnEvent()
    {
        $action = 'index';
        $response = $this->mockResponse();

        $event = new AfterActionEvent($action, $response);

        $this->assertSame($action, $event->getAction());
        $this->assertSame($response, $event->getResponse());
    }
}
