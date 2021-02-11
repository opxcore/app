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

use OpxCore\Profiler\Interfaces\ProfilerInterface;

class AppProfilerProxy implements ProfilerInterface
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
     * @param string $action Action name is used to display name of entry
     * @param int|null $timestamp Externally captured time
     * @param int|null $memory Externally captured memory usage
     *
     * @return  void
     */
    public function start(string $action, ?int $timestamp = null, ?int $memory = null): void
    {
        if ($this->profiler) {
            $this->profiler->start($action, $timestamp, $memory);
        }
    }

    /**
     * Write action to profiling or get whole profiling list.
     *
     * @param string $action Action name is used to display name of entry
     * @param int|null $timestamp Externally captured time
     * @param int|null $memory Externally captured memory usage
     * @param array|null $stacktrace Externally captured stacktrace
     *
     * @return  void
     */
    public function stop(string $action, ?int $timestamp = null, ?int $memory = null, ?array $stacktrace = null): void
    {
        if ($this->profiler) {

            // Capture stack trace and exclude current method call if externally captured stack is not provided.
            if (($stack = $stacktrace) === null) {
                $stack = debug_backtrace(0);
                array_shift($stack);
            }

            $this->profiler->stop($action, $timestamp, $memory, $stack);
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