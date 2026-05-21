<?php
/**
 * This file is taken from Aura.Session and modified.
 */
namespace Ray\WebFormModule;

use SessionHandlerInterface;

final class FakeSessionHandler implements SessionHandlerInterface
{
    private string|null $data = null;

    public function close(): bool
    {
        return true;
    }

    public function destroy(string $id): bool
    {
        $this->data = null;

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return 1;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        return $this->data ?? '';
    }

    public function write(string $id, string $data): bool
    {
        $this->data = $data;

        return true;
    }
}
