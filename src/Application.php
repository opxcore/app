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
    protected int $profilingStartTime;

    /** @var array Timestamp of application start */
    protected array $profilingStopWatches = [];

    /** @var bool Is profiling enabled */
    protected bool $profilingEnabled = true;

    /** @var bool Is application run in debug mode */
    protected bool $debug = false;

    /**
     * Start profiling stopwatch.
     *
     * @param string $action
     *
     * @return  void
     */
    public function profilingStart(string $action): void
    {
        if (!$this->profilingEnabled) {
            return;
        }

        $this->profilingStopWatches[$action] = hrtime(true);
    }

    /**
     * Write action to profiling or get whole profiling list.
     *
     * @param string|null $action
     * @param int|null $time
     * @param int|null $memory
     *
     * @return  void
     */
    public function profilingEnd(?string $action = null, ?int $time = null, ?int $memory = null): void
    {
        if ($this->profilingEnabled === false) {
            return;
        }

        $this->profiling[] = [
            'action' => $action,
            'timestamp' => $time ?? ((int)hrtime(true) - $this->profilingStartTime),
            'time' => $this->profilingStopWatches[$action] ? ((int)hrtime(true) - $this->profilingStopWatches[$action]) : null,
            'memory' => $memory ?? memory_get_usage()
        ];

        unset($this->profilingStopWatches[$action]);
    }

    /**
     * @return  array[]|null
     */
    public function profiling(): ?array
    {
        return $this->profiling;
    }

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
        $this->profilingStart('app.constructor');

        if (!defined('OPXCORE_START')) {
            $this->profilingStartTime = hrtime(true);
        } else {
            $this->profilingStartTime = constant('OPXCORE_START');
            $this->profilingEnd('system.start', 0, constant('OPXCORE_START_MEM'));
        }

        $this->setBasePath($basePath);

        $this->container = $container;
        $this->container->instance('app', $this);

        $this->profilingEnd('app.constructor');
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
        $this->profilingEnd('app.set_base_path');
        $this->basePath = rtrim($basePath, '\/');
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
        $this->profilingEnd('app.get_path');
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
        $this->profilingStart('app.init');

        $this->profilingStart('app.init.config.resolve');
        // Resolve, initialize and load config.
        // Config repository, cache and environment drivers are resolved from container and must be bound outside
        // application in bootstrap file.
        // If configuration interfaces were not bound, container will throw exception.
        /** @var ConfigInterface $config */
        $config = $this->container->make(ConfigInterface::class);
        $this->profilingEnd('app.init.config.resolve');

        $this->profilingStart('app.init.config.load');
        $config->load();
        $this->profilingEnd('app.init.config.load');

        $this->profilingStart('app.init.config.instancing');
        $this->container->instance('config', $config);
        $this->profilingEnd('app.init.config.instancing');

        // Set some parameters loaded with config
//        $this->profilingEnd('app.init.error_handler');
//        $this->debug = $config->get('app.debug', false);

        // Register basic error handler

        $this->profilingEnd('app.init');
    }

    /**
     * Get application config.
     *
     * @return  ConfigInterface
     */
    public function config(): ConfigInterface
    {
        $this->profilingStart('app.config.get');
        $config = $this->container->make('config');
        $this->profilingEnd('app.config.get');

        return $config;
    }

    /**
     * Get logger.
     *
     * @return  \Psr\Log\LoggerInterface
     */
    public function logger(): \Psr\Log\LoggerInterface
    {
        $this->profilingStart('app.logger.get');

        if ($this->container->has('logger')) {
            $logger = $this->container->make('logger');
            $this->profilingEnd('app.logger.get');

            return $logger;
        }

        $this->profilingStart('app.logger.make');

        $logger = $this->container->make(LoggerInterface::class);

        $this->container->instance('logger', $logger);

        $this->profilingEnd('app.logger.make');
        $this->profilingEnd('app.logger.get');

        return $logger;
    }
}