<?php
/*
 * This file is part of the OpxCore.
 *
 * Copyright (c) Lozovoy Vyacheslav <opxcore@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpxCore\Tests\App;

use OpxCore\App\Application;
use OpxCore\Container\Container;
use OpxCore\Profiler\Interfaces\ProfilerInterface;
use OpxCore\Tests\App\Fixtures\TestProfiler;
use PHPUnit\Framework\TestCase;

class ApplicationProfilingTest extends TestCase
{
    public function testAppBasicProfiling(): void
    {
        $container = new Container();
        $profiler = new TestProfiler();
        $container->instance(ProfilerInterface::class, $profiler);
        $app = new Application($container, __DIR__);

        $app->profiler()->start('test');
        self::assertEquals('OpxCore\Tests\App\Fixtures\TestProfiler::start', $profiler->lastCalled);

        $app->profiler()->stop('test');
        self::assertEquals('OpxCore\Tests\App\Fixtures\TestProfiler::stop', $profiler->lastCalled);

        $app->profiler()->enable();
        self::assertEquals('OpxCore\Tests\App\Fixtures\TestProfiler::enable', $profiler->lastCalled);

        $app->profiler()->profiling();
        self::assertEquals('OpxCore\Tests\App\Fixtures\TestProfiler::profiling', $profiler->lastCalled);
    }
}
