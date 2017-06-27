<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP\Action;

abstract class Action implements ActionInterface
{
    public function __toString()
    {
        return self::class;
    }
}
