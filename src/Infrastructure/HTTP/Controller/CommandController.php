<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Response\ResponseResolverInterface;
use League\Tactician\CommandBus;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class CommandController extends Controller
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @param ContainerInterface $container
     * @param ResponseResolverInterface $responseResolver
     * @param CommandBus $commandBus
     */
    public function __construct(
        ContainerInterface $container,
        ResponseResolverInterface $responseResolver,
        EventDispatcherInterface $eventDispatcher,
        CommandBus $commandBus
    ) {
        parent::__construct($container, $responseResolver, $eventDispatcher);

        $this->commandBus = $commandBus;
    }

    /**
     * @param Request $request
     * @param string $action
     * @param array $params
     *
     * @return mixed
     */
    protected function run(Request $request, string $action, array $params)
    {
        return $this->$action(...array_values($params));
    }

    /**
     * @param object $command
     *
     * @return mixed
     */
    protected function dispatchCommand($command)
    {
        return $this->commandBus->handle($command);
    }
}
