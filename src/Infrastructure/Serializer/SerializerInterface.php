<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\Serializer;

interface SerializerInterface
{
    /**
     * @param mixed $data
     *
     * @return array
     */
    public function toArray($data): array;

    /**
     * @param mixed $data
     *
     * @return string
     */
    public function toJSON($data): string;
}
