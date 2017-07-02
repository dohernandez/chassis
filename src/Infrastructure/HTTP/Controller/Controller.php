<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Event\AfterActionEvent;
use Chassis\Infrastructure\HTTP\Event\BeforeActionEvent;
use Chassis\Infrastructure\HTTP\Response\ResponseResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller implements ControllerInterface
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
        $this->beforeAction($action, $request);

        $params = $this->resolveParams($request, $pathParams);

        $data = $this->run($request, $action, $params);

        $response = $this->respond($data);

        $this->afterAction($action, $response);

        return $response;
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
     * @param Request $request
     * @param string $action
     * @param array $params
     *
     * @return mixed
     */
    abstract protected function run(Request $request, string $action, array $params);

    /**
     * @param mixed $data
     *
     * @return Response
     */
    private function respond($data): Response
    {
        if ($data instanceof Response) {
            return $data;
        }

        if ($data === null) {
            return $this->responseResolver->resolve('', Response::HTTP_NO_CONTENT);
        }

        return $this->responseResolver->resolve($data);
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

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
