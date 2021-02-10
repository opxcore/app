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

use OpxCore\App\Interfaces\ProfilerInterface;

class AppProfilerProxy implements Interfaces\ProfilerInterface
{
    /** @var ProfilerInterface|null Profiler instance to call */
    protected ?ProfilerInterface $profiler = null;

    /**
     * Set profiler instance.
     *
     * @param ProfilerInterface|null $profiler
     *
     * @return  void
     */
    public function setProfiler(?ProfilerInterface $profiler): void
    {
        $this->profiler = $profiler;
    }

    /**
     * Start profiling stopwatch.
     *
     * @param string $action
     *
     * @return  void
     */
    public function start(string $action): void
    {
        if ($this->profiler) {
            $this->profiler->start($action);
        }
    }

    /**
     * Write action to profiling or get whole profiling list.
     *
     * @param string $action
     * @param int|null $timestamp
     * @param int|null $memory
     *
     * @return  void
     */
    public function stop(string $action, ?int $timestamp = null, ?int $memory = null): void
    {
        if ($this->profiler) {
            $this->profiler->stop($action . $timestamp, $memory);
        }
    }

    /**
     * Set profiling mode enabled or disabled.
     *
     * @param bool $enable
     *
     * @return  void
     */
    public function enable(bool $enable = true): void
    {
        if ($this->profiler) {
            $this->profiler->enable($enable);
        }
    }

    /**
     * Returns profiling list.
     *
     * @return  array[]|null
     */
    public function profiling(): ?array
    {
        return $this->profiler ? $this->profiler->profiling() : null;
    }
}