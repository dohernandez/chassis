<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP\Response;

use Chassis\Infrastructure\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseResolver implements ResponseResolverInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer = null)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param mixed $data
     * @param int $status
     * @param array $headers
     *
     * @return Response
     */
    public function resolve($data, int $status = Response::HTTP_OK, array $headers = []): Response
    {
        if (is_object($data) || is_array($data)) {
            return new JsonResponse($this->serializer->toJSON($data), $status, $headers, true);
        }

        return new Response($data, !$data ? Response::HTTP_NO_CONTENT : $status, $headers);
    }
}
