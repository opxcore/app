<?php

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