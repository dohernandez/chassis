<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class BeforeActionEvent extends Event
{
    /**
     * @var string
     */
    private $action;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param string $action
     * @param Request $request
     */
    public function __construct(string $action, Request $request)
    {
        $this->action = $action;
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
