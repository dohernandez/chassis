<?php declare(strict_types = 1);

namespace Chassis\Application;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;


class ContainerBuilder
{
    const DEFAULT_CONTAINER_CLASS = "ApplicationContainer";
    const DEFAULT_YML_SERVICES = "services.yml";
    const EVENT_DISPATCHER_SERVICE = 'event_dispatcher';
    const EVENT_LISTENER_TAG = 'event_listener';
    const EVENT_SUBSCRIBER_TAG = 'event_subscriber';

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $appPath;

    /**
     * @var string
     */
    protected $configPath;

    /**
     * @var string
     */
    protected $cachePath;

    /**
     * @var string
     */
    protected $containerClass;

    /**
     * @var string
     */
    protected $containerClassPath;

    /**
     * @var string
     */
    protected $ymlServices;

    /**
     * @var string
     */
    protected $ymlServicesPath;

    public function __construct(
        string $basePath,
        string $ymlServices = self::DEFAULT_YML_SERVICES,
        string $containerClass = self::DEFAULT_CONTAINER_CLASS
    ) {
        $this->basePath = $basePath;
        $this->appPath = sprintf('%s/app', $this->basePath);
        $this->configPath = sprintf('%s/config', $this->basePath);
        $this->cachePath = sprintf('%s/storage/cache', $this->appPath);
        $this->containerClass = $containerClass;
        $this->ymlServices = $ymlServices;
        $this->ymlServicesPath = sprintf('%s/%s', $this->configPath, $this->containerClass);
        $this->containerClassPath = sprintf('%s/%s.php', $this->configPath, $this->containerClass);
    }

    public function buildContainer()
    {
        if (!file_exists($this->containerClassPath)) {
            return $this->loadServicesFromYMLFile();
        }

        return $this->loadServicesFromContainerClass();
    }

    /**
     * @return DependencyInjection\ContainerBuilder
     */
    private function loadServicesFromYMLFile(): DependencyInjection\ContainerBuilder
    {
        $container = new DependencyInjection\ContainerBuilder();
        $loader = new DependencyInjection\Loader\YamlFileLoader($container, new FileLocator(__DIR__));
        $loader->load(sprintf('%s/services.yml', $this->configPath));

        $container->setParameter('app_dir', $this->appPath);
        $container->setParameter('base_dir', $this->basePath);
        $container->setParameter('cache_dir', $this->cachePath);
        $container->setParameter('config_dir', $this->configPath);

        $container->addCompilerPass(new RegisterListenersPass(
            self::EVENT_DISPATCHER_SERVICE,
            self::EVENT_LISTENER_TAG,
            self::EVENT_SUBSCRIBER_TAG
        ));

        $container->compile();

        return $container;
    }

    /**
     * @return ContainerBuilder
     */
    private function loadServicesFromContainerClass()
    {
        require_once $this->containerClassPath;

        $container = new $this->containerClass;
        $container->set('container', $container);

        return $container;
    }
}
