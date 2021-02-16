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

class ApplicationOutputModeTest extends TestCase
{

    public function testOutputMode(): void
    {
        $container = new Container;
        $app = new Application($container, __DIR__);

        self::assertEquals(Application::APP_OUTPUT_HTTP, $app->outputMode());

        $app->outputMode(Application::APP_OUTPUT_CONSOLE);
        self::assertEquals(Application::APP_OUTPUT_CONSOLE, $app->outputMode());

        $app->outputMode(Application::APP_OUTPUT_JSON);
        self::assertEquals(Application::APP_OUTPUT_JSON, $app->outputMode());
    }
}
