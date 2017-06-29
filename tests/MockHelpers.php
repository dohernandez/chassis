<?php

namespace Tests\Chassis;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        return $this->mock(Container::class, $init);
    }

    /**
     * @inheritdoc
     */
    abstract public function prophesize($classOrInterface = null);
}
