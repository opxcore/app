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
use PHPUnit\Framework\TestCase;

class ApplicationProfilingTest extends TestCase
{
    public function testAppBasicProfiling(): void
    {
        $app = new Application(new Container(), __DIR__);
        $app->profilingStart('test');
        $app->profilingEnd('test');
        $profiling = $app->profiling();
        self::assertEquals('test', array_reverse($profiling)[0]['action']);

        $app->profilingEnd('no_start');
        $profiling = $app->profiling();
        self::assertNull(array_reverse($profiling)[0]['time']);
    }

    public function testAppSystemStartProfiling(): void
    {
        define('OPXCORE_START', hrtime(true));
        $app = new Application(new Container(), __DIR__);
        $app->profilingStart('test');
        $app->profilingEnd('test');
        $profiling = $app->profiling();
        self::assertEquals('test', array_reverse($profiling)[0]['action']);
    }

    public function testAppDisableProfiling(): void
    {
        $app = new Application(new Container(), __DIR__);
        $app->profiling(false);
        $app->profilingStart('test');
        $app->profilingEnd('test');
        self::assertNull($app->profiling());
    }
}
