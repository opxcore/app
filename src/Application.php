<?php

namespace OpxCore\App;

use OpxCore\Config\Interfaces\ConfigInterface;
use OpxCore\Container\Interfaces\ContainerExceptionInterface;
use OpxCore\Container\Interfaces\ContainerInterface;
use OpxCore\Container\Interfaces\NotFoundExceptionInterface;
use OpxCore\Log\Interfaces\LoggerInterface;

class Application
{
    /** @var string Project root path. */
    protected string $basePath;

    /** @var ContainerInterface Bound container */
    protected ContainerInterface $container;

    /** @var array Profiling application */
    protected array $profiling = [];

    /** @var int Timestamp of application start */
    protected int $startTime;

    /** @var bool Is profiling enabled */
    protected bool $profilingEnabled = true;

    /**
     * Application constructor.
     *
     * @param ContainerInterface $container
     * @param string $basePath
     *
     * @return  void
     */
    public function __construct(ContainerInterface $container, string $basePath)
    {
        $this->startTime = constant('OPXCORE_START') ?? hrtime(true);

        if (defined('OPXCORE_START')) {
            $this->profiling('system.start', 0, constant('OPXCORE_START_MEM'));
            $this->profiling('app.constructor.start');
        } else {
            $this->profiling('app.constructor.start', 0);
        }


        $this->setBasePath($basePath);

        $this->container = $container;
        $this->container->instance('app', $this);

        $this->profiling('app.constructor.finish');
    }

    /**
     * Write action to profiling or get whole profiling list.
     *
     * @param string|null $action
     * @param int|null $time
     * @param int|null $memory
     *
     * @return null|array[]
     */
    public function profiling(?string $action = null, ?int $time = null, ?int $memory = null): ?array
    {
        if ($this->profilingEnabled === false) {
            return null;
        }

        if ($action === null) {
            return $this->profiling;
        }

        $this->profiling[] = [
            'action' => $action,
            'time' => $time ?? ((int)hrtime(true) - $this->startTime),
            'memory' => $memory ?? memory_get_usage()
        ];

        return null;
    }

    /**
     * Set absolute path for project root.
     *
     * @param string $basePath
     *
     * @return  void
     */
    protected function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '\/');
        $this->profiling('app.setBasePath');
    }

    /**
     * Get absolute path related to project root.
     *
     * @param string|null $to
     *
     * @return  string
     */
    public function path($to = null): string
    {
        return $this->basePath . ($to ? DIRECTORY_SEPARATOR . $to : $to);
    }

    /**
     * Initialize application dependencies.
     *
     * @return  void
     *
     * @throws  ContainerExceptionInterface
     * @throws  NotFoundExceptionInterface
     */
    public function init(): void
    {
        $this->profiling('app.init.start');

        // Resolve, initialize and load config.
        // Config repository, cache and environment drivers are resolved from container and must be bound outside
        // application in bootstrap file.
        // If configuration interfaces were not bound, container will throw exception.
        /** @var ConfigInterface $config */
        $config = $this->container->make(ConfigInterface::class);
        $config->load();
        $this->container->instance('config', $config);

        $this->profiling('app.init.finish');
    }

    /**
     * Get application config.
     *
     * @return  ConfigInterface
     */
    public function config(): ConfigInterface
    {
        $this->profiling('app.config.get');

        return $this->container->make('config');
    }
}