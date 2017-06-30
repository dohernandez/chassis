<?php declare(strict_types = 1);

namespace Chassis\Presentation\Serializer;

use Chassis\Application\Serializer\SerializerInterface;

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
