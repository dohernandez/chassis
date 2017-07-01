<?php declare(strict_types = 1);

namespace Infrastructure\Exception;

use Chassis\Infrastructure\Exception\NotFoundException;
use Faker\Factory;
use LogicException;
use PHPUnit\Framework\TestCase;

class NotFoundExceptionTest extends TestCase
{
    public function testDefaultException()
    {
        $exception = new NotFoundException();

        $this->assertInstanceOf(LogicException::class, $exception);
        $this->assertSame(NotFoundException::DEFAULT_MESSAGE, $exception->getMessage());
    }

    public function testException()
    {
        $faker = Factory::create();

        $message = $faker->realText();
        $exception = new NotFoundException($message);

        $this->assertInstanceOf(LogicException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }
}
