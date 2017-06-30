<?php declare(strict_types=1);

namespace Chassis\Domain;

interface Entity
{
    /**
     * @return mixed
     */
    public function getId();
}
