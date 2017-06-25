<?php declare(strict_types = 1);

use Chassis\Presentation\HTTP\Controller;

$app->get('/', Controller\IndexAction::class);
