<?php declare(strict_types = 1);

namespace Tests\Chassis\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Event\AfterActionEvent;
use Chassis\Infrastructure\HTTP\Event\BeforeActionEvent;
use Chassis\Infrastructure\HTTP\Response\ResponseResolver;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

trait ControllerMockHelpers
{
    /**
     * @param callable|null $responseResolverInit
     *
     * @return ResponseResolver
     */
    protected function mockResponseResolver(callable $responseResolverInit = null): ResponseResolver
    {
        $responseResolver = $this->mock(ResponseResolver::class, $responseResolverInit);

        return $responseResolver;
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

    /**
     * @param callable|null $eventDispatcherInit
     *
     * @return EventDispatcherInterface
     */
    protected function mockEventDispatcher(callable $eventDispatcherInit = null): EventDispatcherInterface
    {
        $eventDispatcher = $this->mock(EventDispatcherInterface::class, $eventDispatcherInit);

        return $eventDispatcher;
    }

    /**
     * @return \Closure
     */
    protected function getEventDispatcherInit(): \Closure
    {
        return function ($eventDispatcher) {
            $eventDispatcher->dispatch(
                BeforeActionEvent::NAME,
                Argument::type(BeforeActionEvent::class)
            )->shouldBeCalled();

            $eventDispatcher->dispatch(
                AfterActionEvent::NAME,
                Argument::type(AfterActionEvent::class)
            )->shouldBeCalled();
        };
    }

    /**
     * @param Response $response
     *
     * @return \Closure
     */
    protected function getResponseResolverInit(Response $response): \Closure
    {
        return function ($responseResolver) use ($response) {
            $responseResolver->resolve(Argument::type('array'))->shouldBeCalled()->willReturn($response);
        };
    }

    /**
     * @param string $jsonContent
     *
     * @return \Closure
     */
    protected function getRequestContentJsonInit(string $jsonContent = '{}'): \Closure
    {
        return function ($request) use ($jsonContent) {
            $request->getContentType()->shouldBeCalled()->willReturn('json');
            $request->getContent()->shouldBeCalled()->willReturn($jsonContent);
            $request->query = new ParameterBag();
        };
    }
}
