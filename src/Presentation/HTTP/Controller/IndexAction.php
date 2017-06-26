<?php declare(strict_types = 1);

namespace Chassis\Presentation\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Action;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Main index page
 */
final class IndexAction extends Action
{
    /**
     * {@inheritdoc}
     */
    public function __exec(Request $request, Response $response, array $args): Response
    {
        return $response->setContent(sprintf('Hello %s', 'Word'));
    }
}
