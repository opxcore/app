<?php


namespace OpxCore\Tests\App\Fixtures;

use OpxCore\Config\Interfaces\ConfigInterface;

class TestConfig implements ConfigInterface
{
    public array $config = [];

    /**
     * @inheritDoc
     */
    public function load(?string $profile = null, ?string $overrides = null, bool $force = false): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value = null): void
    {
        $this->config[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function push(string $key, $value): void
    {
        $this->config[$key][] = $value;
    }
}