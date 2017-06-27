<?php declare(strict_types = 1);

namespace Chassis\Presentation\HTTP\Controller;

use Chassis\Application\Command\IndexCommand;
use Chassis\Infrastructure\HTTP\Controller\CommandController;

class IndexController extends CommandController
{
    public function index()
    {
        return $this->dispatchCommand(new IndexCommand());
    }
}
