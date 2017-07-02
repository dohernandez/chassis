<?php declare(strict_types = 1);

namespace Tests\Chassis\Infrastructure\Logger;

use Chassis\Infrastructure\Logger\LoggerBuilder;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class LoggerBuilderTest extends TestCase
{
    public function testThatItCheckConstants()
    {
        $this->assertSame('.log', LoggerBuilder::LOG_EXTENSION);
    }

    public function testThatItBuildLogger()
    {
        $streamName = getenv('APP_NAME');
        $logPath = __DIR__ . '/sandbox/log';

        $logger = LoggerBuilder::build($streamName, $logPath);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame($streamName, $logger->getName());
        $this->assertSame(1, count($logger->getHandlers()));

        $handler = $logger->getHandlers()[0];

        $this->assertInstanceOf(StreamHandler::class, $handler);

        $this->assertSame(sprintf('%s/%s%s', $logPath, $streamName, LoggerBuilder::LOG_EXTENSION), $handler->getUrl());
        $this->assertSame(Logger::DEBUG, $handler->getLevel());
        $this->assertInstanceOf(LineFormatter::class, $handler->getFormatter());
    }
}
