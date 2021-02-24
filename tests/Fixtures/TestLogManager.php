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
use OpxCore\Log\Interfaces\LogManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class TestLogManager extends AbstractLogger implements LogManagerInterface
{
    public array $logs = [];

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array()): void
    {
        $this->logs[] = ['level' => $level, 'message' => $message, 'context' => $context];
    }

    public function logger($names = null): LoggerInterface
    {
        return new NullLogger();
    }

    public function group($names): LoggerInterface
    {
        return new NullLogger();
    }
}