<?php declare(strict_types = 1);

namespace Chassis\Presentation\Serializer;

use Chassis\Infrastructure\Serializer\SerializerInterface;
use League\Fractal\Manager;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

class Serializer implements SerializerInterface
{
    /**
     * @param mixed $data
     *
     * @return array
     */
    public function toArray($data): array
    {
        return $data;
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    public function toJSON($data): string
    {
        return json_encode($this->toArray($data));
    }
}
