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

use OpxCore\App\Interfaces\AppBootstrapperInterface;
use OpxCore\App\Interfaces\AppInterface;

class TestBootstrapper implements AppBootstrapperInterface
{
    public function bootstrap(AppInterface $app): void
    {
        $app->config()->set('bootstrapped', true);
    }
}