<?php declare(strict_types = 1);

$chassis = new \Chassis\Application\Application(
    new \Chassis\Application\ContainerBuilder('chassis', __DIR__ . DIRECTORY_SEPARATOR . '..')
);

return $chassis;
