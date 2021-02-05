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

use OpxCore\Log\AbstractLogger;
use OpxCore\Log\Interfaces\LoggerInterface;

class TestLogger extends AbstractLogger implements LoggerInterface
{
    public array $logs = [];

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        $this->logs[] = ['level' => $level, 'message' => $message, 'context' => $context];
    }
}