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
use OpxCore\Tests\App\Fixtures\TestBootstrapper;
use OpxCore\Tests\App\Fixtures\TestConfig;
use PHPUnit\Framework\TestCase;

class ApplicationBootstrapTest extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $this->container = new Container;
        $this->container->bind(ConfigInterface::class, TestConfig::class);
    }

    public function testBootstrapper(): void
    {
        $app = new Application($this->container, __DIR__);
        $app->init();
        $app->bootstrap();
        self::assertTrue($app->config()->get('bootstrapped', false));
        self::assertEquals(TestBootstrapper::class, get_class($app->container()->make('test.bootstrapper')));
    }

    public function testBootstrapperNull(): void
    {
        $app = new Application($this->container, __DIR__);
        $app->init();
        $app->bootstrap(null);
        self::assertFalse($app->config()->get('bootstrapped', false));
    }

    public function testBootstrapperArray(): void
    {
        $app = new Application($this->container, __DIR__);
        $app->init();
        $app->bootstrap([TestBootstrapper::class]);
        self::assertTrue($app->config()->get('bootstrapped', false));
    }
}
