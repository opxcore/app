<?php

namespace OpxCore\App;

use OpxCore\Container\Container;
use OpxCore\Config\ConfigEnvironment;
use OpxCore\Interfaces\ConfigInterface;
use OpxCore\Interfaces\ConfigCacheRepositoryInterface;
use OpxCore\Interfaces\ConfigRepositoryInterface;

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
     *
     * @throws  \OpxCore\Container\Exceptions\ContainerException
     * @throws  \OpxCore\Container\Exceptions\NotFoundException
     */
    public function __construct($basePath = null)
    {
        // Apply paths configurations.
        $this->setBasePaths($basePath);

        // Load environment variables
        ConfigEnvironment::load($this->envPath, $this->envFile);

        // Bind container.
        static::setContainer($this);
        $this->instance('app', $this);
        $this->instance(Container::class, $this);

        // Load config files.
        $this->loadConfig();
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
        $this->envPath = $this->path($this->configPath);

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
    public function loadConfig($profile = 'default', $force = false): void
    {
        $config = [];
        $loaded = false;

        // Try to load config from cache first if this option is enabled and driver
        // for config cache was bind.
        if (!$force && (env('CONFIG_CACHE_DISABLE', false) === false) && $this->has(ConfigCacheRepositoryInterface::class)) {

            /** @var \OpxCore\Interfaces\ConfigCacheRepositoryInterface $cacheDriver */
            $cacheDriver = $this->make(ConfigCacheRepositoryInterface::class);

            $loaded = $cacheDriver->load($config, $profile);

            // In this case cache enabled and cache driver exists, but config was not cached.
            $makeCache = !$loaded;
        }

        // The second we try lo create config loader if it was bind and config was not
        // already loaded from cache.
        if (!$loaded && $this->has(ConfigRepositoryInterface::class)) {

            /** @var \OpxCore\Interfaces\ConfigRepositoryInterface $configDriver */
            $configDriver = $this->make(ConfigRepositoryInterface::class);

            $loaded = $configDriver->load($config, $profile);

            // Conditionally make cache for config
            if ($loaded && isset($makeCache, $cacheDriver)) {

                $cacheDriver->save($config, $profile);
            }
        }

        /** @var \OpxCore\Interfaces\ConfigInterface $config */
        $config = $this->make(ConfigInterface::class, ['config' => $config]);

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


}