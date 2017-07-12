<?php declare(strict_types=1);

namespace Tests\Chassis\Infrastructure;

use Closure;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

trait DeferredCallableHelpers
{
    /**
     * @param Request $request
     * @param TestCase $testCase
     *
     * @return Closure
     */
    protected function finalMiddleware(Request $request, TestCase $testCase): Closure
    {
        return function ($middleWareRequest, $middleWareResponse) use ($request, $testCase) {
            $testCase->assertSame($request, $middleWareRequest);

            return $middleWareResponse;
        };
    }

    /**
     * Creates a mock instance.
     *
     * @param string|null $classOrInterface
     * @param callable|null $init
     *
     * @return mixed
     */
    abstract protected function mock($classOrInterface = null, callable $init = null);
}
