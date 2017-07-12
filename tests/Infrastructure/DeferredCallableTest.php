<?php declare(strict_types=1);

namespace Tests\Chassis\Infrastructure;

use Chassis\Infrastructure\DeferredCallable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Chassis\MockHelpers;

class DeferredCallableTest extends TestCase
{
    use DeferredCallableHelpers;
    use MockHelpers;

    public function testThatItCheckConstants()
    {
        $this->assertSame('__invoke', DeferredCallable::CLASS_FUNCTION);
    }

    public function testThatItWhenInvokeWithCallableFunction()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $testCase = $this;


        $deferredCallable = new DeferredCallable(function ($middleWareRequest, $middleWareResponse, $next) {
            return $next($middleWareRequest, $middleWareResponse);
        });

        $next = $this->finalMiddleware($request, $testCase);

        $this->assertSame($response, $deferredCallable($request, $response, $next));
    }

    public function testThatItWhenInvokeWithCallableStringClass()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $testCase = $this;
        $class = 'MiddlewareTest';

        $container = $this->mockContainer(function ($container) use ($class) {
            $container->has($class)->shouldBeCalled()->willReturn(true);
            $container->get($class)->shouldBeCalled()->willReturn(new class() {
                public function __invoke($request, $response, $next)
                {
                    return $next($request, $response);
                }
            });
        });

        $deferredCallable = new DeferredCallable($class, $container);

        $next = $this->finalMiddleware($request, $testCase);

        $this->assertSame($response, $deferredCallable($request, $response, $next));
    }

    public function testThatItThrowAnExceptionWhenInvokeWithNotCallableString()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $class = 'NoCallableMiddlewareTest';

        $container = $this->mockContainer(function ($container) use ($class) {
            $container->has($class)->shouldBeCalled()->willReturn(false);
        });

        $deferredCallable = new DeferredCallable($class, $container);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Callable %s does not exist', $class));

        $deferredCallable($request, $response, null);
    }
}
