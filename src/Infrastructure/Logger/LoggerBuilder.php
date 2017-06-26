<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggerBuilder
{
    /*
     * @var string
     */
    const LOG_EXTENSION = '.log';

    public static function build(string $streamName, string $logPath, int $level = Logger::DEBUG): LoggerInterface
    {
        $stream = new StreamHandler(static::resource($streamName, $logPath), $level);
        $stream->setFormatter(new LineFormatter());

        $logger = new Logger($streamName);
        $logger->pushHandler($stream);

        return $logger;
    }

    /**
     * @param string $streamName
     * @param $logPath
     *
     * @return string
     */
    private static function resource(string $streamName, $logPath): string
    {
        return $logPath . DIRECTORY_SEPARATOR . $streamName . self::LOG_EXTENSION;
    }
}
