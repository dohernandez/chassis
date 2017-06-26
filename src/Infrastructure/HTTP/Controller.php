<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @param ContainerInterface $container
     * @param ResponseResolverInterface $responseResolver
     */
    public function __construct(ContainerInterface $container, ResponseResolverInterface $responseResolver)
    {
        $this->container = $container;
        $this->responseResolver = $responseResolver;
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
        $action = $this->resolveAction($action);
        $params = $this->resolveParams($request, $pathParams);

        $data = $action->__invoke($request, $params);

        $response = $this->resolveResponse($data);

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
}
