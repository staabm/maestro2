<?php

namespace Maestro2\Core\Task;

use Maestro2\Core\Task\Task;

class ComposerTask implements Task
{
    public function __construct(
        private string $path,
        private array $require = [],
        private array $remove = [],
        private bool $update = false,
        private string $group = 'composer',
        private bool $dev = false
    ) {
    }

    public function group(): string
    {
        return $this->group;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function remove(): array
    {
        return $this->remove;
    }

    public function require(): array
    {
        return $this->require;
    }

    public function dev(): bool
    {
        return $this->dev;
    }

    public function update(): bool
    {
        return $this->update;
    }
}