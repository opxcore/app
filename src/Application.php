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

use OpxCore\App\Interfaces\AppInterface;
use OpxCore\App\Interfaces\AppBootstrapperInterface;
use OpxCore\Config\Interfaces\ConfigInterface;
use OpxCore\Container\Interfaces\ContainerExceptionInterface;
use OpxCore\Container\Interfaces\ContainerInterface;
use OpxCore\Container\Interfaces\NotFoundExceptionInterface;
use OpxCore\Profiler\Interfaces\ProfilerInterface;
use OpxCore\Log\Interfaces\LoggerInterface;

class Application implements AppInterface
{
    /** @var string Project root path. */
    protected string $basePath;

    /** @var ContainerInterface|null Bound container */
    protected ContainerInterface $container;

    /** @var bool Is application bootstrapped */
    protected bool $bootstrapped = false;

    /** @var bool Is application run in debug mode */
    protected bool $debug = false;

    /** @var ProfilerInterface|null Profiler to use in application */
    protected ?ProfilerInterface $profiler;

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
        // Capture time and memory usage for profiler
        $timestamp = hrtime(true);
        $memory = memory_get_usage();

        // Create profiler proxy. This will enable calls to profiling not worrying about it was set
        $this->profiler = new AppProfilerProxy;

        // Resolve and create profiler
        if ($container->has(ProfilerInterface::class)) {
            $profilerTimestamp = hrtime(true);
            $profilerMemory = memory_get_usage();
            $profiler = $container->make(ProfilerInterface::class);
            $this->profiler->setProfiler($profiler);
            $this->profiler()->start('app.constructor.profiler.resolve', $profilerTimestamp, $profilerMemory);
            $this->profiler()->stop('app.constructor.profiler.resolve');
        }

        $this->profiler()->start('app.constructor', $timestamp, $memory);

        $this->setBasePath($basePath);

        $this->container = $container;
        $this->container->instance('app', $this);

        $this->profiler()->stop('app.constructor');
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
        $this->profiler->stop('app.set_base_path');

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
        $this->profiler->stop('app.get_path');

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
        $this->profiler()->start('app.init');

        // Resolve, initialize and load config.
        // Config repository, cache and environment drivers are resolved from container and must be bound outside
        // application in bootstrap file.
        // If configuration interfaces were not bound, container will throw exception.
        $this->profiler()->start('app.init.config.resolve');
        /** @var ConfigInterface $config */
        $config = $this->container->make(ConfigInterface::class);
        $this->profiler()->stop('app.init.config.resolve');

        // Load configuration files according config realization
        $this->profiler()->start('app.init.config.load');
        $config->load();
        $this->profiler()->stop('app.init.config.load');

        // Instance config into container for future use
        $this->profiler()->start('app.init.config.instancing');
        $this->container->instance('config', $config);
        $this->profiler()->stop('app.init.config.instancing');

        // Set some parameters loaded with config (or default)
        $this->debug = $config->get('app.debug', false);
        $this->profiler()->enable($config->get('app.profiling', false));

        $this->profiler()->stop('app.init');
    }

    /**
     * Bootstrap application.
     *
     * @return  void
     */
    public function bootstrap(): void
    {
        $this->profiler()->start('app.bootstrap');

        $bootstrappers = $this->config()->get('bootstrappers', []);

        foreach ($bootstrappers as $bootstrapper) {
            /** @var AppBootstrapperInterface $bootstrapper */
            $bootstrapper = $this->container()->make($bootstrapper);
            $bootstrapper->bootstrap($this);
        }

        $this->bootstrapped = true;

        $this->profiler()->stop('app.bootstrap');
    }

    /**
     * Get application config.
     *
     * @return  ConfigInterface
     */
    public function config(): ConfigInterface
    {
        $this->profiler()->start('app.config.get');

        $config = $this->container->make('config');

        $this->profiler()->stop('app.config.get');

        return $config;
    }

    /**
     * Get profiler proxy with assigned profiler (or not assigned)
     *
     * @return  ProfilerInterface
     */
    public function profiler(): ProfilerInterface
    {
        return $this->profiler;
    }

    /**
     * Get logger.s
     *
     * @return  \Psr\Log\LoggerInterface
     */
    public function logger(): \Psr\Log\LoggerInterface
    {
        $this->profiler()->start('app.logger.get');

        // Check for bounded instance of logger in container
        if ($this->container->has('logger')) {
            $logger = $this->container->make('logger');
            $this->profiler()->stop('app.logger.get');

            return $logger;
        }

        // If there is no registered logger resolve it and bind
        $this->profiler()->start('app.logger.make');
        $logger = $this->container->make(LoggerInterface::class);
        $this->container->instance('logger', $logger);
        $this->profiler()->stop('app.logger.make');
        $this->profiler()->stop('app.logger.get');

        return $logger;
    }
}