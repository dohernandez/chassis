<?php declare(strict_types = 1);

namespace Chassis\Presentation\HTTP;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Action implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        return $this->__exec($request, $response, $args);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    abstract protected function __exec(Request $request, Response $response, array $args): Response;

    public function __toString()
    {
        return self::class;
    }
}
