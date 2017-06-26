<?php declare(strict_types = 1);

$chassis = new \Chassis\Infrastructure\Application(
    new \Chassis\Infrastructure\ContainerBuilder('chassis', __DIR__ . DIRECTORY_SEPARATOR . '..')
);

return $chassis;
