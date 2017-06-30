<?php declare(strict_types = 1);

namespace Chassis\Infrastructure;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;


class ContainerBuilder
{
    const DEFAULT_CONTAINER_CLASS = "ApplicationContainer";
    const DEFAULT_YML_SERVICES = "services.yml";
    const EVENT_DISPATCHER_SERVICE = 'app.event_dispatcher';
    const EVENT_LISTENER_TAG = 'app.event_listener';
    const EVENT_SUBSCRIBER_TAG = 'app.event_subscriber';

    /**
     * @var string
     */
    protected $appName;

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
        string $appName,
        string $basePath,
        string $ymlServices = self::DEFAULT_YML_SERVICES,
        string $containerClass = self::DEFAULT_CONTAINER_CLASS
    ) {
        $this->appName = $appName;
        $this->basePath = $basePath;
        $this->appPath = sprintf('%s/app', $this->basePath);
        $this->configPath = sprintf('%s/config', $this->basePath);
        $this->cachePath = sprintf('%s/storage/cache', $this->appPath);
        $this->containerClass = $containerClass;
        $this->ymlServices = $ymlServices;
        $this->ymlServicesPath = sprintf('%s/%s', $this->configPath, $this->ymlServices);
        $this->containerClassPath = sprintf('%s/%s.php', $this->cachePath, $this->containerClass);
    }

    /**
     * @return SymfonyContainerBuilder
     */
    public function build(): SymfonyContainerBuilder
    {
        if (!file_exists($this->containerClassPath)) {
            $container = $this->loadServicesFromYMLFile();

            if ($container->getParameter('app_debug') == "false") {
                $this->dumpContainer($container, $this->containerClassPath, $this->containerClass);
            }

            return $container;
        }

        return $this->loadServicesFromContainerClass();
    }

    /**
     * @return SymfonyContainerBuilder
     */
    private function loadServicesFromYMLFile(): SymfonyContainerBuilder
    {
        $container = new SymfonyContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
        $loader->load($this->ymlServicesPath);

        $container->setParameter('app_name', $this->appName);
        $container->setParameter('app_dir', $this->appPath);
        $container->setParameter('base_dir', $this->basePath);
        $container->setParameter('cache_dir', $this->cachePath);
        $container->setParameter('config_dir', $this->configPath);

        $container->addCompilerPass(new RegisterListenersPass(
            self::EVENT_DISPATCHER_SERVICE,
            self::EVENT_LISTENER_TAG,
            self::EVENT_SUBSCRIBER_TAG
        ));

        $container->compile(true);
        $container->set('app.container', $container);

        return $container;
    }

    /**
     * @param SymfonyContainerBuilder $container
     * @param string $containerFile
     * @param string $containerClass
     */
    private function dumpContainer(SymfonyContainerBuilder $container, string $containerFile, string $containerClass)
    {
        $dir = dirname($containerFile);

        if (!file_exists($dir)) {
            throw new \LogicException("Directory does not exists: $dir."); // @codeCoverageIgnore
        }

        file_put_contents(
            $containerFile,
            (new PhpDumper($container))->dump([ 'class' => $containerClass ])
        );
    }

    /**
     * @return SymfonyContainerBuilder
     */
    private function loadServicesFromContainerClass(): SymfonyContainerBuilder
    {
        require_once $this->containerClassPath;

        $container = new $this->containerClass;
        $container->set('app.container', $container);

        return $container;
    }
}
