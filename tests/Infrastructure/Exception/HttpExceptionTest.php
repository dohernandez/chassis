<?php declare(strict_types = 1);

namespace Infrastructure\Exception;

use Chassis\Infrastructure\Exception\HttpException;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\Chassis\MockHelpers;

class HttpExceptionTest extends TestCase
{
    use MockHelpers;

    public function testException()
    {
        $faker = Factory::create();

        $message = $faker->realText();
        $header = $faker->randomElements();

        $exception = new HttpException(Response::HTTP_NOT_FOUND, $message, null);
        $exception->setHeaders($header);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($header, $exception->getHeaders());
        $this->assertSame(Response::HTTP_NOT_FOUND, $exception->getStatusCode());
    }
}
