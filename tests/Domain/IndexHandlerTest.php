<?php declare(strict_types = 1);

namespace Tests\Chassis\Domain;

use Chassis\Application\Command\IndexCommand;
use Chassis\Domain\IndexHandler;
use PHPUnit\Framework\TestCase;
use Tests\Chassis\MockHelpers;

class IndexHandlerTest extends TestCase
{
    use MockHelpers;

    public function testThatItProcess()
    {
        $httpText = 'Test index chassis';

        $command = $this->mock(IndexCommand::class, function ($command) use ($httpText) {
            $command->getHttpText()->shouldBeCalled()->willReturn($httpText);
        });

        $handle = new IndexHandler();

        $this->assertSame($httpText, $handle->process($command));
    }
}
