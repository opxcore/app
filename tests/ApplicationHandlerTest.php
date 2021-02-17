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
use OpxCore\Config\Interfaces\ConfigInterface;
use OpxCore\Container\Container;
use OpxCore\ExceptionHandler\Interfaces\ExceptionHandlerInterface;
use OpxCore\Tests\App\Fixtures\TestConfig;
use OpxCore\Tests\App\Fixtures\TestHandler;
use PHPUnit\Framework\TestCase;

class ApplicationHandlerTest extends TestCase
{
    public function testHandlerRegistration(): void
    {
        $container = new Container;
        $container->bind(ConfigInterface::class, TestConfig::class);
        $container->singleton(ExceptionHandlerInterface::class, TestHandler::class);
        $app = new Application($container, __DIR__);
        /** @var TestHandler $handler */
        $handler = $app->exceptionHandler();
        self::assertTrue($handler->registered);
    }

    public function testHandlerMissing(): void
    {
        $container = new Container;
        $container->bind(ConfigInterface::class, TestConfig::class);
        $app = new Application($container, __DIR__);
        $handler = $app->exceptionHandler();
        self::assertNull($handler);
    }
}
