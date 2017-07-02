<?php declare(strict_types = 1);

namespace Tests\Chassis\HTTP\Response;

use Chassis\Application\Serializer\SerializerInterface;
use Chassis\Infrastructure\HTTP\Response\ResponseResolver;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Chassis\MockHelpers;

class ResponseResolverTest extends TestCase
{
    use MockHelpers;

    public function testThatItResolveWithResponse()
    {
        $content = 'Chassis Test';
        $serializer = $this->mockSerializer();

        $resolver = new ResponseResolver($serializer);

        $response = $resolver->resolve($content);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($content, $response->getContent());
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @param callable|null $serializerInit
     *
     * @return SerializerInterface
     */
    public function mockSerializer(callable $serializerInit = null): SerializerInterface
    {
        return $this->mock(SerializerInterface::class, $serializerInit);
    }

    public function testThatItResolveWithJsonResponse()
    {
        $data = new stdClass();
        $content = '{\'app:\': \'Chassis Test\'}';
        $serializer = $this->mockSerializer(function ($serializer) use ($data, $content) {
            $serializer->toJSON($data)->shouldBeCalled()->willReturn($content);
        });

        $resolver = new ResponseResolver($serializer);

        $response = $resolver->resolve($data);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($content, $response->getContent());
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
