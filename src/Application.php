<?php

namespace OpxCore\App;

use OpxCore\Config\Config;
use OpxCore\Container\Container;
use OpxCore\Config\ConfigEnvironment;
use OpxCore\Log\LogManager;

class Application extends Container
{
    /**
     * Base application path.
     *
     * @var  string
     */
    protected $basePath;

    /**
     * Environment file path.
     *
     * @var  string
     */
    protected $envPath = '/';

    /**
     * Environment filename.
     *
     * @var  string
     */
    protected $envFile = '.env';

    /**
     * Config files path.
     *
     * @var  string
     */
    protected $configPath = 'config';

    /**
     * Application constructor.
     *
     * @param  string|null $basePath
     */
    public function __construct($basePath = null)
    {
        // Apply paths configurations.
        $this->setBasePaths($basePath);

        // Bind container.
        static::setContainer($this);
        $this->instance('app', $this);
        $this->instance(Container::class, $this);
    }

    /**
     * Set base paths for application.
     *
     * @param  string $basePath
     *
     * @return  $this
     */
    protected function setBasePaths($basePath): self
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->configPath = $this->path($this->configPath);
        $this->envPath = $this->path($this->envPath);

        return $this;
    }

    /**
     * Get absolute path related to project root.
     *
     * @param  string|null $to
     *
     * @return  string
     */
    public function path($to = null): string
    {
        return $this->basePath . ($to ? DIRECTORY_SEPARATOR . $to : $to);
    }

    /**
     * Init application.
     *
     * @throws \OpxCore\Container\Exceptions\ContainerException
     * @throws \OpxCore\Container\Exceptions\NotFoundException
     */
    public function init(): void
    {
        // Load environment variables
        ConfigEnvironment::load($this->envPath, $this->envFile);

        // Create configuration loader and perform loading of configs.
        $this->initConfig();

        // Create logger
        $this->initLogger();
    }

    /**
     * Create configuration for application using dependency injection, load config
     * and instance handler. Notice: ConfigCacheRepositoryInterface::class and
     * ConfigRepositoryInterface::class are resolved from container. They must
     * be bind before calling this method.
     *
     * @param  string $profile Profile name to load config for
     * @param  bool $force Skip loading config from cache driver
     *
     * @throws  \OpxCore\Container\Exceptions\ContainerException
     * @throws  \OpxCore\Container\Exceptions\NotFoundException
     */
    protected function initConfig($profile = null, $force = false): void
    {
        /** @var \OpxCore\Config\Config $config */
        $config = $this->make(Config::class);

        $config->load($profile, $force);

        $this->instance('config', $config);
    }

    /**
     * Register logger.
     *
     * @throws  \OpxCore\Container\Exceptions\ContainerException
     * @throws  \OpxCore\Container\Exceptions\NotFoundException
     */
    protected function initLogger(): void
    {
        $config = $this->config('log');

        $logger = $this->make(LogManager::class, $config);

        $this->instance('log', $logger);
    }

    /**
     * Get configuration for given key or config handler if null given.
     *
     * @param  array|string|int|null $key
     * @param  \Closure|mixed|null $default
     *
     * @return  \OpxCore\Config\Config|mixed|null
     */
    public function config($key = null, $default = null)
    {
        try {
            /** @var \OpxCore\Interfaces\ConfigInterface $config */
            $config = $this->make('config');

            return $key ? $config->get($key, $default) : $config;

        } catch (\Exception $exception) {

            return $default instanceof \Closure ? $default() : $default;
        }
    }

    /**
     * Run application.
     *
     * @return void
     */
    public function run(): void
    {

    }

    /**
     * Get log driver or log manager instance.
     *
     * @param  string|null $driver
     *
     * @return  \OpxCore\Log\LogManager|\Psr\Log\LoggerInterface
     *
     * @throws  \OpxCore\Container\Exceptions\ContainerException
     * @throws  \OpxCore\Container\Exceptions\NotFoundException
     * @throws  \OpxCore\Log\Exceptions\LogManagerException
     */
    public function log($driver = null)
    {
        /** @var \OpxCore\Log\LogManager $logger */
        $logger = $this->make('log');

        if ($driver === null) {
            return $logger;
        }

        if (method_exists($logger, 'driver')) {
            return $logger->driver($driver);
        }

        return $logger;
    }
}