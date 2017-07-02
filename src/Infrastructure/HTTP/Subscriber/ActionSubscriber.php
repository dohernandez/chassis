<?php declare(strict_types=1);

namespace Chassis\Infrastructure\HTTP\Subscriber;

use Chassis\Infrastructure\HTTP\Event\AfterActionEvent;
use Chassis\Infrastructure\HTTP\Event\BeforeActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds timing information to the response.
 */
class ActionSubscriber implements EventSubscriberInterface
{
    const HEADER_X_TOOK = 'X-Took';
    const HEADER_X_TOOK_TO_ACTION = 'X-Took-To-Action';
    const HEADER_X_TOOK_ACTION = 'X-Took-Action';
    const PRIORITY_BEFORE_ACTION = 500;
    const PRIORITY_AFTER_ACTION = -500;

    /*
     * Elapsed time when the request started.
     *
     * @var float
     */
    protected $requestTime;

    /**
     * Elapsed time before the action is invoked.
     *
     * @var float
     */
    private $timeBeforeAction;

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            BeforeActionEvent::NAME => [ 'beforeAction', self::PRIORITY_BEFORE_ACTION ],
            AfterActionEvent::NAME => [ 'afterAction', self::PRIORITY_AFTER_ACTION ],
        ];
    }

    /**
     * @param BeforeActionEvent $event
     * @param float $time
     */
    public function beforeAction(BeforeActionEvent $event, float $time = null)
    {
        $this->requestTime = $event->getRequest()->server->get('REQUEST_TIME_FLOAT');
        $this->timeBeforeAction = $time ?: microtime(true);
    }

    /**
     * @param AfterActionEvent $event
     * @param float $time
     */
    public function afterAction(AfterActionEvent $event, float $time = null)
    {
        $this->applyTimingInfo(
            $event->getResponse(),
            $this->timeBeforeAction,
            $time ?: microtime(true)
        );
    }

    /**
     * @param Response $response
     * @param float $timeBeforeAction
     * @param float $timeAfterAction
     *
     * @return Response
     */
    private function applyTimingInfo(Response $response, float $timeBeforeAction, float $timeAfterAction)
    {
        $headers = $response->headers;
        $headers->set(self::HEADER_X_TOOK, $this->formatRelativeTime());
        $headers->set(self::HEADER_X_TOOK_TO_ACTION, $this->formatRelativeTime($timeBeforeAction));
        $headers->set(self::HEADER_X_TOOK_ACTION, $this->formatTime($timeAfterAction - $timeBeforeAction));

        return $response;
    }

    /**
     * @param float|null $time
     *
     * @return string
     */
    private function formatRelativeTime(float $time = null): string
    {
        return $this->formatTime(($time ?: microtime(true)) - $this->requestTime);
    }

    /**
     * @param float $time
     *
     * @return string
     */
    private function formatTime(float $time): string
    {
        return round($time * 1000, 3) . ' ms';
    }
}
