<?php declare(strict_types = 1);

namespace Tests\Chassis\Exception\Infrastructure;

use Chassis\Infrastructure\Exception\ForbiddenException;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class ForbiddenExceptionTest extends TestCase
{
    public function testException()
    {
        $faker = Factory::create();

        $exception = new ForbiddenException();
        $this->assertSame(ForbiddenException::DEFAULT_MESSAGE, $exception->getMessage());

        $message = $faker->realText();
        $previous = new \Exception();
        $exception = new ForbiddenException($message, $previous);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
