<?php

namespace OpxCore\App;

use OpxCore\Config\Interfaces\ConfigInterface;
use OpxCore\Container\Interfaces\ContainerInterface;
use OpxCore\Log\Interfaces\LoggerInterface;

class Application
{
    /** @var string Project root path. */
    protected string $basePath;

    /** @var ContainerInterface Bound container */
    protected ContainerInterface $container;

    /** @var ConfigInterface Config */
    protected ConfigInterface $config;

    /** @var LoggerInterface Logger */
    protected LoggerInterface $logger;

    /**
     * Application constructor.
     *
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param string $basePath
     */
    public function __construct(ContainerInterface $container, ConfigInterface $config, LoggerInterface $logger, string $basePath)
    {
        $this->setBasePath($basePath);

        $this->container = $container;
        $this->container->instance('app', $this);

        $this->config = $config;

        $this->logger = $logger;
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
}