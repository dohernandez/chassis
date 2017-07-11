<?php declare(strict_types = 1);

namespace Tests\Chassis\Application\Command;

use Chassis\Application\Command\IndexCommand;
use PHPUnit\Framework\TestCase;

class IndexCommandTest extends TestCase
{
    public function testThatItCheckHttpText()
    {
        $command = new IndexCommand();
        $this->assertSame('Welcome to index chassis', $command->getHttpText());
    }
}
