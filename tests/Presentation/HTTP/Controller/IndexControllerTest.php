<?php declare(strict_types = 1);

namespace Tests\Chassis\Presentation\HTTP\Controller;

use Chassis\Application\Command\IndexCommand;
use Chassis\Infrastructure\HTTP\Response\ResponseResolver;
use Chassis\Presentation\HTTP\Controller\IndexController;
use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tests\Chassis\MockHelpers;

class IndexControllerTest extends TestCase
{
    use MockHelpers;

    public function testThatItCheckIndexMethod()
    {
        $container = $this->mockContainer();
        $responseResolver = $this->mock(ResponseResolver::class);
        $eventDispatcher = $this->mock(EventDispatcher::class);
        $commandBus = $this->mock(CommandBus::class, function ($commandBus) {
            $commandBus->handle(Argument::type(IndexCommand::class))->shouldBeCalled()->willReturn(true);
        });

        $controller = new IndexController($container, $responseResolver, $eventDispatcher, $commandBus);

        $request = $this->mockRequest();

        $this->assertTrue($controller->index($request, 'index', []));
    }
}
