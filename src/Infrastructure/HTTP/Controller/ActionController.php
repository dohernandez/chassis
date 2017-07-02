<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Action\Action;
use Chassis\Infrastructure\HTTP\Action\ActionInterface;
use Symfony\Component\HttpFoundation\Request;

class ActionController extends Controller
{
    /**
     * @param Request $request
     * @param string $action
     * @param array $params
     *
     * @return mixed
     */
    protected function run(Request $request, string $action, array $params)
    {
        $action = $this->resolveAction($action);

        return $action->__invoke($request, $params);
    }

    /**
     * @param string $action Action identifier.
     *
     * @return ActionInterface
     */
    protected function resolveAction(string $action): ActionInterface
    {
        $action = $this->getContainer()->get($action);

        if (!$action instanceof ActionInterface) {
            throw new \LogicException(sprintf('Action `%s` must extends `%s`', get_class($action), Action::class));
        }

        return $action;
    }
}
