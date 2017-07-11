<?php declare(strict_types=1);

namespace Chassis\Infrastructure;

use Closure;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeferredCallable
{
    const CLASS_FUNCTION = '__invoke';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var callable|string
     */
    private $callable;

    /**
     * @param callable|string $callable
     * @param ContainerInterface|null $container
     */
    public function __construct($callable, ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->callable = $callable;
    }

    public function __invoke()
    {
        $callable = $this->resolveCallable($this->callable);

        if ($callable instanceof Closure) {
            $callable = $callable->bindTo($this->container);
        }

        $args = func_get_args();

        return call_user_func_array($callable, $args);
    }

    /**
     * @param callable|string $toResolve
     *
     * @return callable
     *
     * @throws RuntimeException if the callable is not resolvable
     * @throws RuntimeException if the callable does not exist
     */
    private function resolveCallable($toResolve)
    {
        if (is_callable($toResolve)) {
            return $toResolve;
        }

        if (!is_string($toResolve)) {
            $this->assertCallable($toResolve);
        }

        $callable = $this->resolveClassCallable($toResolve);
        $this->assertCallable($callable);

        return $callable;
    }

    /**
     * @param Callable $callable
     *
     * @throws RuntimeException if the callable is not resolvable
     */
    private function assertCallable($callable)
    {
        if (!is_callable($callable)) {
            throw new RuntimeException(sprintf(
                '%s is not resolvable',
                is_array($callable) || is_object($callable) ? json_encode($callable) : $callable
            ));
        }
    }

    /**
     * @param string $class
     *
     * @return array
     */
    private function resolveClassCallable(string $class)
    {
        if ($this->container->has($class)) {
            return [$this->container->get($class), self::CLASS_FUNCTION];
        }

        if (!class_exists($class)) {
            throw new RuntimeException(sprintf('Callable %s does not exist', $class));
        }

        return [new $class($this->container), self::CLASS_FUNCTION];
    }
}
