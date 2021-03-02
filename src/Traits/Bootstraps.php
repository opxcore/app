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

use InvalidArgumentException;
use OpxCore\App\Interfaces\AppBootstrapperInterface;

trait Bootstraps
{
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
}