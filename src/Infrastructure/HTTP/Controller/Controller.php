<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Action\Action;
use Chassis\Infrastructure\HTTP\Action\ActionInterface;
use Chassis\Infrastructure\HTTP\Event\AfterActionEvent;
use Chassis\Infrastructure\HTTP\Event\BeforeActionEvent;
use Chassis\Infrastructure\HTTP\Response\ResponseResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Controller implements ControllerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ResponseResolverInterface
     */
    private $responseResolver;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param ContainerInterface $container
     * @param ResponseResolverInterface $responseResolver
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ContainerInterface $container,
        ResponseResolverInterface $responseResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->container = $container;
        $this->responseResolver = $responseResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

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

    /**
     * @param Request $request
     * @param array $pathParams
     *
     * @return array
     */
    private function resolveParams(Request $request, array $pathParams): array
    {
        $params = $request->getContentType() === 'json'
            ? (array) json_decode($request->getContent(), true)
            : iterator_to_array($request->request);

        return array_merge($params, iterator_to_array($request->query), $pathParams);
    }

    /**
     * @param string $action
     * @param Request $request
     */
    protected function beforeAction(string $action, Request $request)
    {
        $this->eventDispatcher->dispatch(
            BeforeActionEvent::NAME,
            new BeforeActionEvent($action, $request)
        );
    }

    /**
     * @param mixed $data
     *
     * @return Response
     */
    private function resolveResponse($data): Response
    {
        if ($data instanceof Response) {
            return $data;
        }

        if ($data === null) {
            return $this->respond('', 204);
        }

        return $this->respond($data);
    }

    /**
     * @param null $data
     * @param int $status
     * @param array $headers
     *
     * @return Response
     */
    protected function respond($data = null, int $status = Response::HTTP_OK, array $headers = []): Response
    {
        return $this->responseResolver->resolve($data, $status, $headers);
    }

    /**
     * @param string $action
     * @param Response $response
     */
    protected function afterAction(string $action, Response $response)
    {
        $this->eventDispatcher->dispatch(
            AfterActionEvent::NAME,
            new AfterActionEvent($action, $response)
        );
    }
}
