<?php declare(strict_types=1);

namespace Tests\Chassis;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait MockHelpers
{
    /**
     * Creates a mock instance.
     *
     * @param string|null $classOrInterface
     * @param callable|null $init
     *
     * @return mixed
     */
    protected function mock($classOrInterface = null, callable $init = null)
    {
        $prophecy = $this->prophesize($classOrInterface);

        if ($init) {
            $init($prophecy);
        }

        return $prophecy->reveal();
    }

    /**
     * @param callable|null $init
     *
     * @return ContainerInterface
     */
    protected function mockContainer(callable $init = null): ContainerInterface
    {
        return $this->mock(ContainerInterface::class, $init);
    }

    /**
     * @inheritdoc
     */
    abstract public function prophesize($classOrInterface = null);

    /**
     * @param callable $init
     *
     * @return Request
     */
    protected function mockRequest(callable $init = null): Request
    {
        return $this->mock(Request::class, $init);
    }

    /**
     * @param callable $init
     *
     * @return Response
     */
    protected function mockResponse(callable $init = null): Response
    {
        return $this->mock(Response::class, $init);
    }
}
