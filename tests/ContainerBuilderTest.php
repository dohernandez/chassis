<?php declare(strict_types = 1);

namespace Tests\Chassis;

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
        return __DIR__ . "/sandbox/app/storage/cache/$containerClass.php";
    }

    public function testBuildContainerFromYML()
    {
        $base_dir = __DIR__ . '/sandbox';
        $containerClass = self::CONTAINER_CLASS;
        $containerFilename = self::formatContainerFilename($containerClass);
        $this->assertFileNotExists($containerFilename);

        $ymlServices = self::YML_SERVICES;
        $container = (new ContainerBuilder(self::APP_NAME, $base_dir, $ymlServices, $containerClass))->build();
        $this->assertFileExists($containerFilename);
        $this->assertSame($base_dir, $container->getParameter('base_dir'));
    }
}
