<?php declare(strict_types = 1);

namespace Tests\Chassis\Infrastructure\Exception;

use Chassis\Infrastructure\Exception\HttpException;
use Chassis\Infrastructure\Exception\MethodNotAllowedHttpException;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Tests\Chassis\MockHelpers;

class MethodNotAllowedHttpExceptionTest extends TestCase
{
    use MockHelpers;

    public function testException()
    {
        $faker = Factory::create();

        $message = $faker->realText();

        $exception = new MethodNotAllowedHttpException([ 'POST' ], $message);

        $this->assertInstanceOf(HttpException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame([ 'Allow' => 'POST' ], $exception->getHeaders());
    }
}
