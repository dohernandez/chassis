<?php declare(strict_types = 1);

namespace Chassis\Presentation\HTTP\Controller;

use Chassis\Infrastructure\HTTP\Action\Action;
use Symfony\Component\HttpFoundation\Request;

/**
 * Main index page
 */
final class IndexAction extends Action
{
    /**
     * @param Request $request
     * @param array $args
     *
     * @return mixed
     */
    public function __invoke(Request $request, array $args)
    {
        return 'Welcome to action chassis';
    }
}
