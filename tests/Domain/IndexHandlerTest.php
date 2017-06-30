<?php declare(strict_types = 1);

namespace Chassis\Domain;

use Chassis\Application\Command\IndexCommand;
use PHPUnit\Framework\TestCase;
use Tests\Chassis\MockHelpers;

class IndexHandlerTest extends TestCase
{
    use MockHelpers;

    public function testHandle()
    {
        $command = $this->mock(IndexCommand::class);

        $handle = new IndexHandler();

        $this->assertSame('Welcome to index chassis', $handle->handle($command));
    }
}
