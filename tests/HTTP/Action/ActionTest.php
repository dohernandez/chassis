<?php declare(strict_types = 1);

namespace Tests\Chassis\HTTP\Action;

use Chassis\Infrastructure\HTTP\Action\Action;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\Chassis\MockHelpers;

class ActionTest extends TestCase
{
    use MockHelpers;

    public function testThatItStringReturnClassName()
    {
        $action = new class() extends Action {
            public function __invoke(Request $request, array $args)
            {
                return true;
            }
        };

        $this->assertSame(get_class($action), (string) $action);
    }
}
