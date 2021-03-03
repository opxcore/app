<?php
/*
 * This file is part of the OpxCore.
 *
 * Copyright (c) Lozovoy Vyacheslav <opxcore@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpxCore\App\Traits;

use OpxCore\Config\Interfaces\ConfigInterface;
use OpxCore\Container\Interfaces\ContainerInterface;
use OpxCore\ExceptionHandler\Interfaces\ExceptionHandlerInterface;
use OpxCore\Log\Interfaces\LogManagerInterface;
use OpxCore\Profiler\Interfaces\ProfilerInterface;

trait AppServices
{
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
}