<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Action\Action;
use Chassis\Infrastructure\HTTP\Action\ActionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActionController extends Controller
{
    /**
     * @param Request $request
     * @param string $action
     * @param array $pathParams
     *
     * @return Response
     */
    final public function __invoke(Request $request, string $action, array $pathParams): Response
    {
        $strAction = $action;

        $action = $this->resolveAction($strAction);
        $params = $this->resolveParams($request, $pathParams);

        $this->beforeAction($strAction, $request);

        $data = $action->__invoke($request, $params);

        $response = $this->resolveResponse($data);

        $this->afterAction($strAction, $response);

        return $response;
    }

    /**
     * @param string $action Action identifier.
     *
     * @return ActionInterface
     */
    protected function resolveAction(string $action): ActionInterface
    {
        $action = $this->container->get($action);

        if (!$action instanceof ActionInterface) {
            throw new \LogicException("Action `$action` must extends `" . Action::class . "`.");
        }

        return $action;
    }
}
