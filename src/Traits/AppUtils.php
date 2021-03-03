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

trait AppUtils
{
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