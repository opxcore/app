<?php

namespace OpxCore\App;

use OpxCore\Container\Container;
use OpxCore\Config\ConfigEnvironment;
use OpxCore\Interfaces\ConfigInterface;
use OpxCore\Interfaces\LogManagerInterface;

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
     * Init application.
     *
     * @throws \OpxCore\Container\Exceptions\ContainerException
     * @throws \OpxCore\Container\Exceptions\NotFoundException
     */
    public function init(): void
    {
        // Load environment variables
        ConfigEnvironment::load($this->envPath, $this->envFile);

        // Load config files.
        $this->loadConfig();

        // Bind logger
        $this->registerLogger();
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
     * @param $basePath
     *
     * @return  $this
     */
    public function setBasePaths($basePath): self
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
     * Load configuration files for application and instance config.
     *
     * @param  string $profile Profile name to load config for
     * @param  bool $force Skip cache
     *
     * @throws  \OpxCore\Container\Exceptions\ContainerException
     * @throws  \OpxCore\Container\Exceptions\NotFoundException
     */
    public function loadConfig($profile = null, $force = false): void
    {
        /** @var \OpxCore\Interfaces\ConfigInterface $config */
        $config = $this->make(ConfigInterface::class);

        $config->load($profile, $force);

        $this->instance('config', $config);
    }

    /**
     * Get config.
     *
     * @param  array|string|int|null $key
     * @param  \Closure|mixed|null $default
     *
     * @return  \OpxCore\Interfaces\ConfigInterface|mixed|null
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
     * Register logger.
     *
     * @throws  \OpxCore\Container\Exceptions\ContainerException
     * @throws  \OpxCore\Container\Exceptions\NotFoundException
     */
    public function registerLogger(): void
    {
        $config = $this->config('log');

        $logger = $this->make(LogManagerInterface::class, $config);

        $this->instance('log', $logger);
    }

    /**
     * Get log manager instance.
     *
     * @return  \OpxCore\Interfaces\LogManagerInterface
     *
     * @throws  \OpxCore\Container\Exceptions\ContainerException
     * @throws  \OpxCore\Container\Exceptions\NotFoundException
     */
    public function log(): LogManagerInterface
    {
        return $this->make('log');
    }
}