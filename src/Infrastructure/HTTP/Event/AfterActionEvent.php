<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class AfterActionEvent extends Event
{
    const NAME = 'http.after.action';

    /**
     * @var string
     */
    private $action;

    /**
     * @var Response
     */
    private $response;

    /**
     * @param string $action
     * @param Response $response
     */
    public function __construct(string $action, Response $response)
    {
        $this->action = $action;
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
