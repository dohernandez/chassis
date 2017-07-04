<?php declare(strict_types = 1);

namespace Tests\Chassis\Presentation\Serializer;

use Chassis\Presentation\Serializer\Serializer;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class SerializerTest extends TestCase
{
    public function testThatItSerializeIntoArray()
    {
        $faker = Factory::create();

        $data = $faker->randomElements();

        $serialize = new Serializer();

        $this->assertSame($data, $serialize->toArray($data));
    }

    public function testThatItSerializeIntoJSON()
    {
        $faker = Factory::create();

        $data = $faker->randomElements();

        $serialize = new Serializer();

        $this->assertSame(json_encode($data), $serialize->toJSON($data));
    }
}
