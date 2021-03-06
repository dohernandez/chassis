<?php declare(strict_types = 1);

namespace Chassis\Domain;

use Chassis\Application\Command\IndexCommand;

class IndexHandler
{
    public function process(IndexCommand $command)
    {
        return $command->getHttpText();
    }
}
