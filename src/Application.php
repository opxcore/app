<?php
/*
 * This file is part of the OpxCore.
 *
 * Copyright (c) Lozovoy Vyacheslav <opxcore@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpxCore\App;

use OpxCore\App\Interfaces\AppBootstrapperInterface;
use OpxCore\App\Interfaces\AppInterface;
use OpxCore\Config\Interfaces\ConfigInterface;
use OpxCore\Container\Interfaces\ContainerExceptionInterface;
use OpxCore\Container\Interfaces\ContainerInterface;
use OpxCore\Container\Interfaces\NotFoundExceptionInterface;
use OpxCore\Log\Interfaces\LoggerInterface;

class Application implements AppInterface
{
    /** @var string Project root path. */
    protected string $basePath;

    /** @var ContainerInterface|null Bound container */
    protected ContainerInterface $container;

    /** @var array Profiling application */
    protected array $profiling = [];

    /** @var int Timestamp of application start */
    protected int $profilingStartTime;

    /** @var array Timestamp of application start */
    protected array $profilingStopWatches = [];

    /** @var bool Is profiling enabled */
    protected bool $profilingEnabled = true;

    /** @var bool Is application bootstrapped */
    protected bool $bootstrapped = false;

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
     * @param int|null $timestamp
     * @param int|null $memory
     *
     * @return  void
     */
    public function profilingEnd(?string $action = null, ?int $timestamp = null, ?int $memory = null): void
    {
        if ($this->profilingEnabled === false) {
            return;
        }

        $executionTime = array_key_exists($action, $this->profilingStopWatches) ? ((int)hrtime(true) - $this->profilingStopWatches[$action]) : null;
        $timeStamp = $timestamp ?? ((int)hrtime(true) - $this->profilingStartTime - $executionTime ?? 0);

        $stack = debug_backtrace(0);
        array_shift($stack);

        $this->profiling[] = [
            'action' => $action,
            'timestamp' => $timeStamp,
            'time' => $executionTime,
            'memory' => $memory ?? memory_get_usage(),
            'stack' => $stack,
        ];

        unset($this->profilingStopWatches[$action]);
    }

    /**
     * Returns profiling list or set profiling mode.
     *
     * @param bool|null $enable
     *
     * @return  array[]|null
     */
    public function profiling(?bool $enable = null): ?array
    {
        // If parameter is bool set profiling mode
        if (is_bool($enable)) {
            $this->profilingEnabled = $enable;

            return null;
        }

        if (!$this->profilingEnabled) {
            return null;
        }

        // Order profiled items by timestamp first
        usort($this->profiling, static function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });

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
            $this->profilingEnd('system.start', 0, @constant('OPXCORE_START_MEM'));
        }

        $this->setBasePath($basePath);

        $this->container = $container;
        $this->container->instance('app', $this);

        $this->profilingEnd('app.constructor');
    }

    /**
     * Get container registered in application.
     *
     * @return  ContainerInterface
     */
    public function container(): ContainerInterface
    {
        return $this->container;
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
     * Weaver the application is in debug mode.
     *
     * @return  bool
     */
    public function isDebugMode(): bool
    {
        return $this->debug;
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

        // Resolve, initialize and load config.
        // Config repository, cache and environment drivers are resolved from container and must be bound outside
        // application in bootstrap file.
        // If configuration interfaces were not bound, container will throw exception.
        $this->profilingStart('app.init.config.resolve');
        /** @var ConfigInterface $config */
        $config = $this->container->make(ConfigInterface::class);
        $this->profilingEnd('app.init.config.resolve');

        // Load configuration files according config realization
        $this->profilingStart('app.init.config.load');
        $config->load();
        $this->profilingEnd('app.init.config.load');

        // Instance config into container for future use
        $this->profilingStart('app.init.config.instancing');
        $this->container->instance('config', $config);
        $this->profilingEnd('app.init.config.instancing');

        // Set some parameters loaded with config
        $this->debug = $config->get('app.debug', false);
//        $this->profilingEnd('app.init.error_handler');
        // Register basic error handler

        $this->profilingEnd('app.init');
    }

    /**
     * Bootstrap application.
     *
     * @return  void
     */
    public function bootstrap(): void
    {
        $this->profilingStart('app.bootstrap');

        $bootstrappers = $this->config()->get('bootstrappers', []);

        foreach ($bootstrappers as $bootstrapper) {
            /** @var AppBootstrapperInterface $bootstrapper */
            $bootstrapper = $this->container()->make($bootstrapper);
            $bootstrapper->bootstrap($this);
        }

        $this->bootstrapped = true;

        $this->profilingEnd('app.bootstrap');
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

        // Check for bounded instance of logger in container
        if ($this->container->has('logger')) {
            $logger = $this->container->make('logger');
            $this->profilingEnd('app.logger.get');

            return $logger;
        }

        // If there is no registered logger resolve it and bind
        $this->profilingStart('app.logger.make');
        $logger = $this->container->make(LoggerInterface::class);
        $this->container->instance('logger', $logger);
        $this->profilingEnd('app.logger.make');

        $this->profilingEnd('app.logger.get');

        return $logger;
    }
}