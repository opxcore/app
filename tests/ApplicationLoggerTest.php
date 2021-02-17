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
use OpxCore\Log\Interfaces\LoggerInterface;
use OpxCore\Tests\App\Fixtures\TestConfig;
use OpxCore\Tests\App\Fixtures\TestLogger;
use PHPUnit\Framework\TestCase;

class ApplicationLoggerTest extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $this->container = new Container;
        $this->container->bind(ConfigInterface::class, TestConfig::class);
        $this->container->bind(LoggerInterface::class, TestLogger::class);
    }

    public function testAppLogger(): void
    {
        $app = new Application($this->container, __DIR__);
        $app->init();

        $logger = $app->logger();
        self::assertEquals(TestLogger::class, get_class($logger));

        $logger = $app->logger();
        self::assertEquals(TestLogger::class, get_class($logger));
    }
}
