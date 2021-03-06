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
use OpxCore\Tests\App\Fixtures\TestConfig;
use OpxCore\Container\Container;
use OpxCore\Container\Interfaces\ContainerExceptionInterface;
use OpxCore\Log\Interfaces\LoggerInterface;
use OpxCore\Tests\App\Fixtures\TestLogManager;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $this->container = new Container;
        $this->container->bind(ConfigInterface::class, TestConfig::class);
        $this->container->bind(LoggerInterface::class, TestLogManager::class);
    }

    public function testAppBasic(): void
    {
        $app = new Application($this->container, __DIR__);
        self::assertEquals($this->container, $app->container());
        self::assertEquals($app, $app->container()->make('app'));
        self::assertEquals(__DIR__, $app->path());
        self::assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'test', $app->path('test'));
    }

    public function testAppInitNoConfig(): void
    {
        $container = new Container;
        $app = new Application($container, __DIR__);
        $this->expectException(ContainerExceptionInterface::class);
        $app->init();
    }

    public function testAppInit(): void
    {
        $app = new Application($this->container, __DIR__);
        $app->init();
        $config = $app->config();
        self::assertEquals(TestConfig::class, get_class($config));
        self::assertTrue($app->isDebugMode());
    }
}
