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

use InvalidArgumentException;
use OpxCore\App\Interfaces\AppInterface;
use OpxCore\App\Interfaces\AppBootstrapperInterface;
use OpxCore\Config\Interfaces\ConfigInterface;
use OpxCore\Container\Interfaces\ContainerExceptionInterface;
use OpxCore\Container\Interfaces\ContainerInterface;
use OpxCore\Container\Interfaces\NotFoundExceptionInterface;
use OpxCore\ExceptionHandler\Interfaces\ExceptionHandlerInterface;
use OpxCore\Log\Interfaces\LogManagerInterface;
use OpxCore\Profiler\Interfaces\ProfilerInterface;

class Application implements AppInterface
{
    /** @var string Project root path. */
    protected string $basePath;

    /** @var ContainerInterface Bound container */
    protected ContainerInterface $container;

    /** @var bool Is application bootstrapped */
    protected bool $bootstrapped = false;

    /** @var bool Is application run in debug mode */
    protected bool $debug = false;

    /** @var ProfilerInterface|null Profiler to use in application */
    protected ?ProfilerInterface $profiler;

    /** @var int Output mode of application. Used by other services to determine how to format output */
    protected int $outputMode = self::APP_OUTPUT_HTTP;

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
        $this->container->instance(AppInterface::class, $this);

        // Register exception handler.
        $this->profiler()->start('app.constructor.handler.register');

        if (($handler = $this->exceptionHandler()) !== null) {
            $handler->register();
        }

        $this->profiler()->stop('app.constructor.handler.register');

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
     * Get exception handler if it was bound.
     *
     * @return  ExceptionHandlerInterface|null
     */
    public function exceptionHandler(): ?ExceptionHandlerInterface
    {
        $this->profiler->start('app.exceptionHandler.resolve');

        if (!$this->container()->has(ExceptionHandlerInterface::class)) {
            $this->profiler->stop('app.exceptionHandler.resolve');
            return null;
        }
        $handler = $this->container()->make(ExceptionHandlerInterface::class);

        $this->profiler->stop('app.exceptionHandler.resolve');

        return $handler;
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
     * If null passed to $bootstrappers no bootstrapper would be processed.
     * Default `bootstrappers` or other string value would be used as key
     * to get bootstrappers list  from application config.
     * If array passed, it will be used as array of bootstrappers.
     *
     * @param string|array|null $bootstrappers
     *
     * @return  void
     */
    public function bootstrap($bootstrappers = 'bootstrappers'): void
    {
        $this->profiler()->start('app.bootstrap');

        // If null value passed, just mark application bootstrapped. No need other actions.
        if (is_null($bootstrappers)) {
            $this->bootstrapped = true;
            $this->profiler()->stop('app.bootstrap');
            return;
        }

        // If string passed, load configuration for given key
        if (is_string($bootstrappers)) {
            $bootstrappers = $this->config()->get($bootstrappers, []);
        }

        // Iterate and bootstrap all of bootstrappers
        foreach ($bootstrappers as $bootstrapper => $dependencies) {

            // Check if bootstrapper was given without dependencies
            if (is_numeric($bootstrapper)) {
                $bootstrapper = $dependencies;
                $dependencies = [];
            }

            $this->profiler()->start("app.bootstrap: {$bootstrapper}");

            /** @var AppBootstrapperInterface $bootstrapperInstance */
            $bootstrapperInstance = $this->container()->make($bootstrapper, $dependencies);

            if (!$bootstrapperInstance instanceof AppBootstrapperInterface) {
                throw new InvalidArgumentException(
                    'Bootstrapper [' . get_class($bootstrapperInstance)
                    . '] should be instance of ' . AppBootstrapperInterface::class
                );
            }

            $shouldBeInstanced = $bootstrapperInstance->bootstrap($this);

            if ($shouldBeInstanced !== null) {
                foreach ($shouldBeInstanced as $key => $instance) {
                    $this->container()->instance($key, $instance);
                }
            }

            $this->profiler()->stop("app.bootstrap: {$bootstrapper}");
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
     * Get profiler proxy with assigned profiler (or not assigned).
     *
     * @return  ProfilerInterface
     */
    public function profiler(): ProfilerInterface
    {
        return $this->profiler;
    }

    /**
     * Get logger.
     *
     * @return  LogManagerInterface
     */
    public function log(): LogManagerInterface
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
        $logger = $this->container->make(LogManagerInterface::class);
        $this->container->instance('logger', $logger);
        $this->profiler()->stop('app.logger.make');
        $this->profiler()->stop('app.logger.get');

        return $logger;
    }

    /**
     * Get application output mode is to be used.
     *
     * @param int|null $mode Mode to be set.
     *
     * @return  int
     */
    public function outputMode(?int $mode = null): int
    {
        if ($mode !== null) {
            $this->outputMode = $mode;
        }

        return $this->outputMode;
    }


}