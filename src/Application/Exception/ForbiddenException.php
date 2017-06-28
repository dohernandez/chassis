<?php

namespace Chassis\Application\Exception;

class ForbiddenException extends \Exception
{
    const DEFAULT_MESSAGE = "User doesn't have the required permission.";

    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct(string $message = self::DEFAULT_MESSAGE, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
