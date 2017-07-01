<?php declare(strict_types = 1);

namespace Infrastructure\Exception;

use Chassis\Infrastructure\Exception\HttpException;
use Chassis\Infrastructure\Exception\NotFoundHttpException;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\Chassis\MockHelpers;

class NotFoundHttpExceptionTes extends TestCase
{
    use MockHelpers;

    public function testException()
    {
        $faker = Factory::create();

        $message = $faker->realText();

        $exception = new NotFoundHttpException($message);

        $this->assertInstanceOf(HttpException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(Response::HTTP_NOT_FOUND, $exception->getStatusCode());
    }
}
