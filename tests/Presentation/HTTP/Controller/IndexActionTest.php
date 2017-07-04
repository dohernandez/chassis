<?php declare(strict_types = 1);

namespace Tests\Chassis\Presentation\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Action\Action;
use Chassis\Presentation\HTTP\Controller\IndexAction;
use PHPUnit\Framework\TestCase;
use Tests\Chassis\MockHelpers;

class IndexActionTest extends TestCase
{
    use MockHelpers;

    public function testThatItCheckInvoke()
    {
        $request = $this->mockRequest();
        $action = new IndexAction();

        $this->assertInstanceOf(Action::class, $action);
        $this->assertSame('Welcome to action chassis', $action->__invoke($request, []));
    }
}
