<?php
/*
 * This file is part of the OpxCore.
 *
 * Copyright (c) Lozovoy Vyacheslav <opxcore@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpxCore\Tests\App\Fixtures;


class TestProfiler implements \OpxCore\Profiler\Interfaces\ProfilerInterface
{
    /** @var string Last called method */
    public string $lastCalled = '';

    /** @var array|null Last called arguments */
    public ?array $lastCalledArgs = null;

    /**
     * @inheritDoc
     */
    public function start(string $action, ?int $timestamp = null, ?int $memory = null): void
    {
        $this->lastCalled = __METHOD__;
        $this->lastCalledArgs = func_get_args();
    }

    /**
     * @inheritDoc
     */
    public function stop(string $action, ?int $timestamp = null, ?int $memory = null, ?array $stacktrace = null): void
    {
        $this->lastCalled = __METHOD__;
        $this->lastCalledArgs = func_get_args();
    }

    /**
     * @inheritDoc
     */
    public function enable(bool $enable = true): void
    {
        $this->lastCalled = __METHOD__;
        $this->lastCalledArgs = func_get_args();
    }

    /**
     * @inheritDoc
     */
    public function profiling(): ?array
    {
        $this->lastCalled = __METHOD__;
        $this->lastCalledArgs = func_get_args();

        return null;
    }
}