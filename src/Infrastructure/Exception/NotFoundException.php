<?php declare(strict_types=1);

namespace Chassis\Infrastructure\Exception;

class NotFoundException extends \LogicException
{
    const DEFAULT_MESSAGE = 'Resource not found.';

    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct(string $message = self::DEFAULT_MESSAGE, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
