<?php declare(strict_types = 1);

namespace Tests\Chassis\Infrastructure;

use Chassis\Infrastructure\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class ContainerBuilderTest extends TestCase
{
    const CONTAINER_CLASS = 'TestApplicationContainer';
    const APP_NAME = 'Chassis Test';
    const YML_SERVICES = 'services.yml';

    public static function setupBeforeClass()
    {
        $containerClass = self::CONTAINER_CLASS;
        $containerFilename = self::formatContainerFilename($containerClass);

        if (file_exists($containerFilename)) {
            unlink($containerFilename);
        }
    }

    private static function formatContainerFilename(string $containerClass): string
    {
        return __DIR__ . "/../sandbox/app/storage/cache/$containerClass.php";
    }

    public function testThatItCheckConstant()
    {
        $this->assertSame(ContainerBuilder::BASE_YML_SERVICES_PATH, 'services/services.yml');
        $this->assertSame(ContainerBuilder::DEFAULT_CONTAINER_CLASS, 'ApplicationContainer');
        $this->assertSame(ContainerBuilder::DEFAULT_YML_SERVICES, 'services.yml');
        $this->assertSame(ContainerBuilder::EVENT_DISPATCHER_SERVICE, 'app.event_dispatcher');
        $this->assertSame(ContainerBuilder::EVENT_LISTENER_TAG, 'app.event_listener');
        $this->assertSame(ContainerBuilder::EVENT_SUBSCRIBER_TAG, 'app.event_subscriber');
    }

    public function testBuildContainerFromYML()
    {
        $base_dir = __DIR__ . '/../sandbox';
        $containerClass = self::CONTAINER_CLASS;
        $containerFilename = self::formatContainerFilename($containerClass);
        $this->assertFileNotExists($containerFilename);

        $ymlServices = self::YML_SERVICES;
        $container = (new ContainerBuilder(self::APP_NAME, $base_dir, $ymlServices, $containerClass))->build();
        $this->assertFileExists($containerFilename);
        $this->assertSame($base_dir, $container->getParameter('base_dir'));
    }

    /**
     * @depends testBuildContainerFromYML
     */
    public function testThatItBuildContainerFromClass()
    {
        $base_dir = __DIR__ . '/../sandbox';
        $containerClass = self::CONTAINER_CLASS;
        $containerFilename = self::formatContainerFilename($containerClass);
        $this->assertFileExists($containerFilename);

        $ymlServices = self::YML_SERVICES;
        $container = (new ContainerBuilder(self::APP_NAME, $base_dir, $ymlServices, $containerClass))->build();
        $this->assertSame($base_dir, $container->getParameter('base_dir'));
    }
}
