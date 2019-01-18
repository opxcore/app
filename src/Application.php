<?php

namespace OpxCore\App;

use OpxCore\Config\Config;
use OpxCore\Container\Container;

class Application extends Container
{
    /**
     * Base application directory.
     *
     * @var  string
     */
    protected $basePath;

    /**
     * Config files directory.
     *
     * @var  string
     */
    protected $configPath;

    /**
     * Application constructor.
     *
     * @param  string|null $basePath
     *
     * @return  void
     */
    public function __construct($basePath = null)
    {
        // Apply paths configurations.
        $this->setBasePaths($basePath);

        // Base bindings for container.
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

        $this->configPath = $this->basePath . DIRECTORY_SEPARATOR . 'config';

        return $this;
    }

    /**
     * Load configuration files for application and instance config.
     */
    public function loadConfig(): void
    {
        $config = new Config($this->basePath, $this->configPath, $this->configPath);

        $this->instance('config', $config);
    }

    /**
     * Get config.
     *
     * @param  array|string|int|null $key
     * @param  mixed|null $default
     *
     * @return  \OpxCore\Config\Config|mixed|null
     */
    public function config($key = null, $default = null)
    {
        try {
            /** @var Config $config */
            $config = $this->make('config');
            return $key ? $config->get($key, $default) : $config;
        } catch (\Exception $exception) {
            return null;
        }
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
}